<?php

namespace SmartCore\Bundle\EngineBundle\Engine;

use Symfony\Component\DependencyInjection\ContainerAware;
use SmartCore\Bundle\EngineBundle\Container;

class Module extends ContainerAware
{
    protected $modules = array();
    private $initialized = false;
    
    /**
     * Initializes the collection of modules.
     */
    private function initialize()
    {
        if (empty($this->modules)) {
            $sql = "SELECT * FROM engine_modules";
            $result = $this->container->get('engine.db')->query($sql);
            while ($row = $result->fetchObject()) {
                $this->modules[$row->module_id] = array(
                    'class'             => $row->class,
                    'install_datetime'  => $row->install_datetime,
                    'user_id'           => $row->user_id,
                );
            }
            
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
        if (!$this->initialized) {
            $this->initialize();
        }
                
        return $this->modules;
    }
    
    /**
     * Получить информацию о модуле.
     */
    public function get($name)
    {
        if (!$this->initialized) {
            $this->initialize();
        }
        
        if (isset($this->modules[$name])) {
            return $this->modules[$name];
        }

        return null;
    }
}
