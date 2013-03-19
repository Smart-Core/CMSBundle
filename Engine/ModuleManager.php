<?php

namespace SmartCore\Bundle\EngineBundle\Engine;

use Symfony\Component\DependencyInjection\ContainerAware;

class ModuleManager extends ContainerAware
{
    protected $modules = array();
    protected $initialized = false;
    protected $configFile;
    
    /**
     * Initializes the collection of modules.
     */
    public function initialize()
    {
        if (!$this->initialized) {
            $this->configFile = $this->container->get('kernel')->getRootDir() . '/usr/modules.ini';

            $this->modules = parse_ini_file($this->configFile);
            $this->initialized = true;
        }
    }
        
    /**
     * Получить список всех модулей.
     * 
     * @return array
     */
    public function all()
    {
        return $this->modules;
    }
    
    /**
     * Получить информацию о модуле.
     */
    public function get($name)
    {
        if (isset($this->modules[$name])) {
            return $this->modules[$name];
        } else {
            return null;
        }
    }
}
