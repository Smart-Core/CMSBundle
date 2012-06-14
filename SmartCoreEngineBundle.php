<?php

namespace SmartCore\Bundle\EngineBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use SmartCore\Bundle\EngineBundle\DependencyInjection\Compiler\TemplateResourcesPass;

class SmartCoreEngineBundle extends Bundle
{
    protected $modules = array();
    
	public function boot()
	{
        Container::set($this->container);
		require_once '_temp.php';
	}
    
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
//        $container->addCompilerPass(new TemplateResourcesPass());
    }
    
    /**
     * Динамические сущности - "Модули", наподобии бандлов.
     * 
     * @todo продумать наследование, как у бандлов.
     * @todo сделать создание объктов.
     */
    public function getModule($name, $first = true)
    {
        if ($name == 2) {
            $name = 'SmartCoreTexterModule';
        }
        
        if (!isset($this->modules[$name])) {
            $this->modules[$name][0] = new \SmartCore\Module\Texter\SmartCoreTexterModule();
        }
                
        if (true === $first) {
            return $this->modules[$name][0];
        } else {
            return $this->modules[$name];
        }
    }    
}