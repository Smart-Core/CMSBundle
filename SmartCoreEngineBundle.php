<?php

namespace SmartCore\Bundle\EngineBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use SmartCore\Bundle\EngineBundle\DependencyInjection\Compiler\TemplateResourcesPass;

class SmartCoreEngineBundle extends Bundle
{
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
}