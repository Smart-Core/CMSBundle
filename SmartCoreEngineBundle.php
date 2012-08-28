<?php

namespace SmartCore\Bundle\EngineBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use SmartCore\Bundle\EngineBundle\DependencyInjection\Compiler\TemplateResourcesPass;

class SmartCoreEngineBundle extends Bundle
{
    protected $modules_cache = array();
    protected $modules = array();
    
    public function boot()
    {
        Container::set($this->container);
        
        if ($this->container->get('kernel')->getEnvironment() == 'prod' and $this->container->has('db.logger')) {
            $this->container->get('engine.db')->getConfiguration()->setSQLLogger($this->container->get('db.logger'));
        }
        
        require_once '_temp.php';
    }
    
    /*
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
//        $container->addCompilerPass(new TemplateResourcesPass());
    }
    */
    
    /**
     * Динамические сущности - "Модули", наподобии бандлов.
     * 
     * @todo продумать наследование, как у бандлов.
     *
    public function __getModule($name, $first = true)
    {
        if (empty($this->modules_cache)) {
            $this->modules_cache = $this->container->get('engine.module')->all();
        }
        
        if (!isset($this->modules[$name])) {
            $this->modules[$name][0] = new $this->modules_cache[substr($name, 0, strlen($name) - 6)]['class']();
        }
        
        if (true === $first) {
            return $this->modules[$name][0];
        } else {
            return $this->modules[$name];
        }
    }
    */
}