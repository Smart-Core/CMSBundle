<?php

namespace SmartCore\Bundle\EngineBundle\Engine;

use SmartCore\Bundle\EngineBundle\Controller\Controller;
use SmartCore\Bundle\EngineBundle\Container;

class Module extends Controller
{
    protected $modules = array();
    private $initialized = false;
    
    /**
     * Initializes the collection of modules.
     */
    private function initialize()
    {
        if (empty($this->modules)) {
            $sql = "SELECT * FROM {$this->container->get('engine.db')->prefix()}engine_modules";
            $result = $this->container->get('engine.db')->query($sql);
            while ($row = $result->fetchObject()) {
                $this->modules[$row->module_id] = array(
                    'class'             => $row->class,
                    'descr'             => $row->descr,
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
        } else {
            return null;
        }
    }
}