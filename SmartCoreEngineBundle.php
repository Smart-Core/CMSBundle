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
        $container->addCompilerPass(new TemplateResourcesPass());
    }
    */

}
