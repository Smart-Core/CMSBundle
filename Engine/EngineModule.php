<?php

namespace SmartCore\Bundle\CMSBundle\Engine;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;

class EngineModule
{
    /**
     * @var \AppKernel
     */
    protected $kernel;

    /**
     * @var \SmartCore\Bundle\CMSBundle\Module\ModuleBundle[]
     */
    protected $modules = [];

    /**
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;

        foreach ($this->kernel->getModules() as $module_name => $data) {
//            $reflector = new \ReflectionClass($data['class']);
//            $this->modules[$module_name] = $this->kernel->getBundle($reflector->getShortName());
            $this->modules[$module_name] = $this->kernel->getBundle($module_name.'ModuleBundle');
        }
    }

    /**
     * Получение списка всех модулей.
     *
     * @return \SmartCore\Bundle\CMSBundle\Module\ModuleBundle[]
     */
    public function all()
    {
        return $this->modules;
    }

    /**
     * Получение информации о модуле.
     *
     * @param string $name
     *
     * @return \SmartCore\Bundle\CMSBundle\Module\ModuleBundle|null
     */
    public function get($name)
    {
        return isset($this->modules[$name]) ? $this->modules[$name] : null;
    }

    /**
     * @param string $moduleName
     *
     * @return array
     */
    public function getThemes($moduleName)
    {
        $dir = $this->kernel->getBundle('SiteBundle')->getPath().'/Resources/modules/'.$moduleName;

        if (!is_dir($dir)) {
            return [];
        }

        $finder = new Finder();
        $finder->directories()->sortByName()->depth('== 0')->in($dir);

        $themes = [];
        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder as $file) {
            $themes[] = $file->getFilename();
        }

        return $themes;
    }

    /**
     * Проверить, подключен ли модуль.
     *
     * @param string $name
     *
     * @return bool
     */
    public function has($name)
    {
        return isset($this->modules[$name]) ? true : false;
    }

    /**
     * Установка модуля.
     *
     * @param string $filename
     *
     * @todo доделать.
     */
    public function install($filename)
    {
        $rootDir = $this->kernel->getRootDir();
        $distDir = $rootDir.'/../dist';

        // 1) Распаковка архива.
        $zip = new \ZipArchive();
        $zip->open($distDir.'/'.$filename);
        $zip->extractTo($rootDir.'/../src');

        // 2) Подключение модуля.
        $modulesList = $this->kernel->getModules();
        $modulesList['Example'] = '\SmartCore\Module\Example\ExampleModule'; // @todo ['class'] and ['path']
        ksort($modulesList);

        $modulesIni = '';
        foreach ($modulesList as $key => $value) {
            $modulesIni .= "$key = $value\n";
        }

        file_put_contents($rootDir.'/usr/modules.ini', $modulesIni);

        // 3) Очистка кэша.
        $finderCache = new Finder();
        $finderCache->ignoreDotFiles(false)
            ->ignoreVCS(true)
            ->depth('== 0')
            ->in($this->kernel->getCacheDir().'/../');

        $fs = new Filesystem();
        /** @var \Symfony\Component\Finder\SplFileInfo $file*/
        foreach ($finderCache as $file) {
            try {
                $fs->remove($file->getPath());
            } catch (IOException $e) {
                // do nothing
            }
        }

        // 4) Установка ресурсов (Resources/public).
        $application = new Application($this->kernel); // Symfony\Bundle\FrameworkBundle\Console\Application
        $application->setAutoExit(false);
        $input = new ArrayInput(['command' => 'assets:install', 'target' => $rootDir.'/../web']);
        $output = new BufferedOutput();
        $retval = $application->run($input, $output);
    }
}
