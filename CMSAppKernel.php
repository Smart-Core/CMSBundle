<?php

namespace SmartCore\Bundle\CMSBundle;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

abstract class CMSAppKernel extends BaseKernel
{
    const VERSION = 'v0.2';

    /** @var string  */
    protected $siteName = null;

    /** @var array */
    protected $modules = [];

    /**
     * @return \Symfony\Component\HttpKernel\Bundle\BundleInterface[]
     */
    public function registerBundles()
    {
        $bundles = [];

        $this->registerSmartCoreCmsBundles($bundles);

        return $bundles;
    }

    /**
     * {@inheritdoc}
     */
    public function getBundle($name, $first = true)
    {
        $moduleNname = str_replace('ModuleBundle', 'Module', $name);

        if (isset($this->bundleMap[$moduleNname])) {
            $name = $moduleNname;
        }

        return parent::getBundle($name, $first);
    }

    /**
     * Boots the current kernel.
     *
     * @api
     */
    public function boot()
    {
        if (true === $this->booted) {
            return;
        }

        parent::boot();

        \Profiler::setKernel($this);
    }

    /**
     * Prepares the ContainerBuilder before it is compiled.
     *
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    protected function prepareContainer(ContainerBuilder $container)
    {
        parent::prepareContainer($container);
        $container->setParameter('smart_core_cms.modules', $this->modules);
        $container->setParameter('smart_core_cms.site_name', $this->siteName);
    }

    /**
     * Получить список подключенных модулей CMS.
     *
     * @return array
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * Получить данные о подключенном модуле.
     *
     * @param $name
     *
     * @return mixed
     */
    public function getModule($name)
    {
        if (isset($this->modules[$name])) {
            return $this->modules[$name];
        }
    }

    /**
     * @return string
     */
    public function getSiteName()
    {
        return $this->siteName;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Bundle\BundleInterface[] $bundles
     */
    protected function registerSmartCoreCmsBundles(&$bundles)
    {
        $this->registerCmsDependencyBundles($bundles);
        $this->autoRegisterSiteBundle($bundles);
        $this->registerCmsModules($bundles);
    }

    /**
     * @param \Symfony\Component\HttpKernel\Bundle\BundleInterface[] $bundles
     */
    protected function registerCmsModules(&$bundles)
    {
        $cacheModules = $this->getCacheDir().'/smart_core_cms_modules_enabled.meta';

        if (!$this->debug and file_exists($cacheModules)) {
            $this->modules = unserialize(file_get_contents($cacheModules));
            foreach ($this->modules as $module) {
                $module_class = $module['class'];
                $bundles[] = new $module_class();
            }

            return;
        }

        $reflector = new \ReflectionClass(end($bundles));
        $modulesConfig = dirname($reflector->getFileName()).'/Resources/config/modules.ini';

        // Чтение списка модулей. Т.е. модули подключаются почти динамически.
        if (file_exists($modulesConfig)) {
            foreach (parse_ini_file($modulesConfig) as $module_name => $module_class) {
                if (class_exists($module_class)) {
                    $bundles[] = new $module_class();
                    $reflector = new \ReflectionClass($module_class);
                    $this->modules[$module_name] = [
                        'class'     => $module_class,
                        'path'      => end($bundles)->getPath(),
                        'namespace' => $reflector->getNamespaceName(),
                    ];
                } else {
                    // @todo сообщение об отсутсвии класса.
                }

                if (is_dir(dirname($cacheModules))) {
                    file_put_contents($cacheModules, serialize($this->modules), LOCK_EX);
                }
            }
        }
    }

    /**
     * @param \Symfony\Component\HttpKernel\Bundle\BundleInterface[] $bundles
     */
    protected function registerCmsDependencyBundles(&$bundles)
    {
        /**
         * Not support for Symfony 3.x
         *
         * "happyr/slugify-bundle": "*",
         * "mremi/templating-extra-bundle": "dev-master",
         * "jms/debugging-bundle": "dev-master",
         */
        $bundles[] = new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle();
        $bundles[] = new \Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle();
        $bundles[] = new \Symfony\Bundle\FrameworkBundle\FrameworkBundle();
        $bundles[] = new \Symfony\Bundle\SecurityBundle\SecurityBundle();
        $bundles[] = new \Symfony\Bundle\TwigBundle\TwigBundle();
        $bundles[] = new \Symfony\Bundle\MonologBundle\MonologBundle();
        $bundles[] = new \Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle();
        $bundles[] = new \Symfony\Bundle\AsseticBundle\AsseticBundle();
        $bundles[] = new \Symfony\Bundle\DebugBundle\DebugBundle();

        $bundles[] = new \Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle();
        $bundles[] = new \FM\ElfinderBundle\FMElfinderBundle();
        $bundles[] = new \FOS\RestBundle\FOSRestBundle();
        $bundles[] = new \FOS\UserBundle\FOSUserBundle();
        $bundles[] = new \Genemu\Bundle\FormBundle\GenemuFormBundle();
        $bundles[] = new \JMS\SerializerBundle\JMSSerializerBundle();
        //$bundles[] = new \HWI\Bundle\OAuthBundle\HWIOAuthBundle();
        $bundles[] = new \Knp\Bundle\GaufretteBundle\KnpGaufretteBundle();
        //$bundles[] = new \Knp\Bundle\DisqusBundle\KnpDisqusBundle();
        $bundles[] = new \Knp\Bundle\MenuBundle\KnpMenuBundle();
        //$bundles[] = new \Knp\RadBundle\KnpRadBundle();
        $bundles[] = new \Liip\ThemeBundle\LiipThemeBundle();
        $bundles[] = new \Liip\ImagineBundle\LiipImagineBundle();
        $bundles[] = new \Mopa\Bundle\BootstrapBundle\MopaBootstrapBundle();
        $bundles[] = new \RaulFraile\Bundle\LadybugBundle\RaulFraileLadybugBundle();
        $bundles[] = new \RickySu\TagcacheBundle\TagcacheBundle();
        $bundles[] = new \Smart\CoreBundle\SmartCoreBundle();
        $bundles[] = new \SmartCore\Bundle\CMSBundle\CMSBundle();
        $bundles[] = new \SmartCore\Bundle\FelibBundle\FelibBundle();
        $bundles[] = new \SmartCore\Bundle\HtmlBundle\HtmlBundle();
        $bundles[] = new \SmartCore\Bundle\MediaBundle\SmartMediaBundle();
        $bundles[] = new \SmartCore\Bundle\RichEditorBundle\SmartRichEditorBundle();
        $bundles[] = new \SmartCore\Bundle\SimpleProfilerBundle\SmartSimpleProfilerBundle();
        $bundles[] = new \SmartCore\Bundle\SeoBundle\SmartSeoBundle();
        $bundles[] = new \SmartCore\Bundle\SessionBundle\SmartCoreSessionBundle();
        $bundles[] = new \SmartCore\Bundle\SettingsBundle\SmartSettingsBundle();
        //$bundles[] = new \SmartCore\Module\Unicat\UnicatBundle();
        $bundles[] = new \Sonata\IntlBundle\SonataIntlBundle();
        $bundles[] = new \Stfalcon\Bundle\TinymceBundle\StfalconTinymceBundle(); // "stfalcon/tinymce-bundle": "v0.2.1",
        $bundles[] = new \WhiteOctober\BreadcrumbsBundle\WhiteOctoberBreadcrumbsBundle();
        $bundles[] = new \WhiteOctober\PagerfantaBundle\WhiteOctoberPagerfantaBundle();
        // $bundles[] = new \Zenstruck\Bundle\ImagineExtraBundle\ZenstruckImagineExtraBundle(); // "zenstruck/imagine-extra-bundle": "*"

        if (in_array($this->getEnvironment(), ['dev', 'test'])) {
            $bundles[] = new \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new \Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new \Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new \Elao\WebProfilerExtraBundle\WebProfilerExtraBundle();
            //$bundles[] = new \SensioLabs\DoctrineQueryStatisticsBundle\SensioLabsDoctrineQueryStatisticsBundle(); // "sensiolabs/doctrine-query-statistics-bundle": "dev-master",
            $bundles[] = new \SmartCore\Bundle\CMSGeneratorBundle\CMSGeneratorBundle();
        }
    }

    /**
     * @param \Symfony\Component\HttpKernel\Bundle\BundleInterface[] $bundles
     *
     * @throws \LogicException
     */
    protected function autoRegisterSiteBundle(&$bundles)
    {
        // Сначала производится попытка подключить указанный вручную сайт.
        if (!empty($this->siteName)) {
            $siteBundleClass = '\\'.$this->siteName.'SiteBundle\SiteBundle';

            if (class_exists($siteBundleClass)) {
                $bundles[] = new $siteBundleClass();

                return;
            }
        }

        $cacheSiteName = $this->getCacheDir().'/smart_core_cms_site_name.meta';
        if (file_exists($cacheSiteName)) {
            $this->siteName = file_get_contents($cacheSiteName);
            $siteBundleClass = '\\'.$this->siteName.'SiteBundle\\SiteBundle';
            if (class_exists($siteBundleClass)) {
                $bundles[] = new $siteBundleClass();

                return;
            }
        }

        $finder = (new Finder())->directories()->depth('== 0')->name('*SiteBundle')->name('SiteBundle')->in($this->rootDir.'/../src');

        // Такой подсчет работает быстрее, чем $finder->count();
        $count = 0;
        $dirName = null;

        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder as $file) {
            $count++;
            $dirName = $file->getBasename();
        }

        if ($count == 0 and isset($_SERVER['HTTP_HOST'])) {
            die('Не доступен SiteBundle, сгенерируйте его командой <pre>$ bin/console cms:generate:sitebundle</pre>');
        }

        if ($count > 1) {
            $response = 'Trying to register two bundles with the same name "SiteBundle"</br></br>Found in /src/:</br>';
            foreach ($finder as $file) {
                $response .= $file->getBasename().'</br>';
            }

            if (isset($_SERVER['HTTP_HOST'])) {
                die($response);
            } else {
                throw new \LogicException(str_replace('</br>', "\n", $response));
            }
        } else {
            $className = '\\'.$dirName.'\\SiteBundle';
            if (class_exists($className)) {
                $bundles[] = new $className();
                $this->siteName = str_replace('SiteBundle', '', $dirName);

                if (is_dir(dirname($cacheSiteName))) {
                    file_put_contents($cacheSiteName, $this->siteName, LOCK_EX);
                }
            }
        }
    }

    /**
     * @param LoaderInterface $loader
     * @return void
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->rootDir.'/config/config_'.$this->getEnvironment().'.yml');
    }

    /**
     * Размещение кеша в /var/.
     *
     * @return string
     */
    public function getCacheDir()
    {
        return $this->rootDir.'/../var/cache/'.$this->environment;
    }

    /**
     * Размещение логов в /var/.
     *
     * @return string
     */
    public function getLogDir()
    {
        return $this->rootDir.'/../var/logs';
    }
}
