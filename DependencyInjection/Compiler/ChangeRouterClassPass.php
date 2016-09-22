<?php

namespace SmartCore\Bundle\CMSBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ChangeRouterClassPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $router = $container->getDefinition('router.default');

        $router->setClass($container->getParameter('router.class'));
    }
}
