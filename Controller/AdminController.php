<?php

namespace SmartCore\Bundle\CMSBundle\Controller;

use Smart\CoreBundle\Controller\Controller;
use SmartCore\Bundle\CMSBundle\CMSAppKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;

class AdminController extends Controller
{
    /**
     * @return Response
     */
    public function indexAction()
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            return $this->render('@CMS/User/login.html.twig');
        }

        $dashboard = [];

        foreach ($this->get('cms.module')->all() as $module) {
            $data = $module->getDashboard();

            if (!empty($data['items'])) {
                $dashboard[$module->getShortName()] = $data;
            }
        }

        return $this->render('@CMS/Admin/index.html.twig', [
            'dashboard' => $dashboard,
        ]);
    }

    /**
     * @return Response
     */
    public function notFoundAction()
    {
        return $this->render('@CMS/Admin/not_found.html.twig');
    }

    /**
     * @return Response
     */
    public function reportsAction()
    {
        return $this->render('@CMS/Admin/reports.html.twig', [
            'system' => [
                'php' => $this->getPhpSettings(),
                'platform' => $this->getPlatformInfo(),
            ],
        ]);
    }

    /**
     * Получить информацию о платформе.
     *
     * @return array
     */
    protected function getPlatformInfo()
    {
        $db = $this->get('database_connection');

        $data = [];
        $data[] = [
            'title' => 'Smart Core CMS version',
            'value' => CMSAppKernel::VERSION,
            'required' => '',
            'recomended' => '',
            'hint' => '',
            'warning' => 0,
        ];
        $isDebug = $this->get('kernel')->isDebug() ? 'true' : 'false';
        $data[] = [
            'title' => 'Symfony Framework version',
            'value' => Kernel::VERSION.' (debug = '.$isDebug.')',
            'required' => '',
            'recomended' => '',
            'hint' => '',
            'warning' => 0,
        ];
        $data[] = [
            'title' => 'Database server',
            'value' => $db->query('SHOW VARIABLES LIKE "%version_comment%"')->fetchObject()->Value.' version '.$db->query('SELECT version() AS version')->fetchObject()->version,
            'required' => 'MySQL 5.1',
            'recomended' => 'MySQL 5.5+ or PostgreSQL 9.3+',
            'hint' => '',
            'warning' => 0,
        ];
        $data[] = [
            'title' => 'Web Server',
            'value' => $_SERVER['SERVER_SOFTWARE'].' ('.php_sapi_name().')',
            'required' => '',
            'recomended' => '',
            'hint' => '',
            'warning' => 0,
        ];

        return $data;
    }

    /**
     * Получить настрйоки PHP.
     *
     * @return array
     */
    protected function getPhpSettings()
    {
        $accelerators = '';

        if (function_exists('apc_store')) {
            if (ini_get('apc.enabled')) {
                $accelerators = 'APC';
            } else {
                $accelerators = 'APC (disabled)';
            }
        }

        if (function_exists('apcu_store')) {
            if (ini_get('apc.enabled')) {
                $accelerators = 'APCu';
            } else {
                $accelerators = 'APCu (disabled)';
            }
        }

        if (extension_loaded('wincache') and ini_get('wincache.ocenabled')) {
            if (!empty($accelerators)) {
                $accelerators .= ', ';
            }

            $accelerators .= 'Wincache (opcache)';
        }

        if (extension_loaded('wincache') and ini_get('wincache.ucenabled')) {
            if (!empty($accelerators)) {
                $accelerators .= ', ';
            }

            $accelerators .= 'Wincache (usercache)';
        }

        if (function_exists('xcache_set') and (int) ini_get('xcache.var_size') > 0) {
            if (!empty($accelerators)) {
                $accelerators .= ', ';
            }

            $accelerators .= 'xcache';
        }

        if (extension_loaded('Zend OPcache') and ini_get('opcache.enable')) {
            if (!empty($accelerators)) {
                $accelerators .= ', ';
            }

            $accelerators .= 'Zend OPcache';
        }

        $data = [];
        $data[] = [
            'title' => 'PHP Version',
            'value' => phpversion().' ('.php_uname('m').')',
            'required' => '7.1.0',
            'recomended' => '7.1.6+',
            'hint' => '',
            'warning' => 0,
        ];
        $data[] = [
            'title' => 'PHP Accelerators',
            'value' => $accelerators,
            'required' => '',
            'recomended' => 'APCu, Zend OPcache, Wincache',
            'hint' => '',
            'warning' => 0,
        ];
        $data[] = [
            'title' => 'Memory Limit',
            'value' => ini_get('memory_limit'),
            'required' => '128M',
            'recomended' => '256M',
            'hint' => '',
            'warning' => 0,
        ];
        $data[] = [
            'title' => 'Safe Mode',
            'value' => ini_get('safe_mode') ? 'On' : 'Off',
            'required' => 'Off',
            'recomended' => '',
            'hint' => '',
            'warning' => 0,
        ];
        $data[] = [
            'title' => 'Display Errors',
            'value' => ini_get('display_errors') ? 'On' : 'Off',
            'required' => 'Off',
            'recomended' => '',
            'hint' => '',
            'warning' => 0,
        ];
        $data[] = [
            'title' => 'Magic Quotes',
            'value' => ini_get('magic_quotes_gpc') ? 'On' : 'Off',
            'required' => 'Off',
            'recomended' => '',
            'hint' => '',
            'warning' => 0,
        ];
        $data[] = [
            'title' => 'Register Globals',
            'value' => ini_get('register_globals') ? 'On' : 'Off',
            'required' => 'Off',
            'recomended' => '',
            'hint' => '',
            'warning' => 0,
        ];
        $data[] = [
            'title' => 'Output Buffering',
            'value' => ((bool)ini_get('output_buffering')) ? 'On' : 'Off',
            'required' => 'On',
            'recomended' => '',
            'hint' => '',
            'warning' => 0,
        ];
        $data[] = [
            'title' => 'Mbstring Enabled',
            'value' => extension_loaded('mbstring') ? 'Yes' : 'No',
            'required' => 'Yes',
            'recomended' => '',
            'hint' => '',
            'warning' => 0,
        ];
        $data[] = [
            'title' => 'Upload max filesize',
            'value' => ini_get('upload_max_filesize'),
            'required' => '4M',
            'recomended' => '20M',
            'hint' => '',
            'warning' => 0,
        ];
        $data[] = [
            'title' => 'Server time',
            'value' => (new \DateTime())->format('Y-m-d H:i:s').' ('.ini_get('date.timezone').')',
            'required' => '',
            'recomended' => '',
            'hint' => '',
            'warning' => 0,
        ];

        return $data;
    }
    
    /**
     * Renders Elfinder.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function elfinderAction()
    {
        return $this->render('@CMS/Admin/elfinder.html.twig', [
            'fullscreen'    => true,
            'includeAssets' => $this->container->getParameter('fm_elfinder')['instances']['default']['include_assets'],
            'prefix'        => $this->container->getParameter('fm_elfinder')['assets_path'],
            'theme'         => $this->container->getParameter('fm_elfinder')['instances']['default']['theme'],
        ]);
    }

    /**
     * Отображение списка модулей.
     *
     * @return Response
     */
    public function moduleAction()
    {
        return $this->render('@CMS/Admin/module.html.twig', [
            'modules' => $this->get('cms.module')->all(),
        ]);
    }

    /**
     * @param string $filename
     */
    public function moduleInstallAction($filename = null)
    {
        $finder = new Finder();

        if (is_dir($this->get('kernel')->getRootDir().'/../dist')) {
            $finder
                ->ignoreDotFiles(false)
                ->ignoreVCS(true)
                ->name('*.zip')
                ->in($this->get('kernel')->getRootDir().'/../dist');
        } else {
            $finder = [];
        }

        // @todo убрать в сервис.
        if (!empty($filename)) {
            $this->get('cms.module')->install($filename);
        }

        return $this->render('@CMS/Admin/module_install.html.twig', [
            'modules'  => $finder,
            'filename' => $filename,
        ]);
    }

    /**
     * AJAX обновление БД.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function moduleInstallUpdateDbAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $application = new Application($this->get('kernel'));
            $application->setAutoExit(false);
            $input = new ArrayInput(['command' => 'doctrine:schema:update', '--force' => true]);
            $output = new BufferedOutput();

            $retval = $application->run($input, $output);

            return new Response('БД успешно обновлена.<p>'.$output->fetch().'</p>');
        } else {
            return new Response('Обновление БД возможно только через AJAX.');
        }
    }

    /**
     * @return Response
     */
    public function backupIndexAction()
    {
        $dbdumper = $this->get('smart_db_dumper.manager');

        $finder = new Finder();
        $files = $finder->ignoreDotFiles(true)->in($dbdumper->getBackupsDir().$dbdumper->getPlatform());

        return $this->render('@CMS/Admin/backup.html.twig', [
            'files'  => $files,
        ]);
    }

    /**
     * @param string $filename
     *
     * @return Response
     */
    public function backupDownloadAction($filename)
    {
        $dbdumper = $this->get('smart_db_dumper.manager');

        $path = $dbdumper->getBackupsDir().$dbdumper->getPlatform().'/'.$filename;

        if (file_exists($path)) {
            $response = new Response();
            $response
                ->setContent(file_get_contents($path))
                ->headers->add([
                    'Content-Disposition' => 'attachment; filename="'.$filename.'"'
                ])
            ;

            return $response;
        } else {
            return $this->notFoundAction();
        }
    }
}
