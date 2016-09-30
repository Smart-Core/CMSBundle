<?php

namespace SmartCore\Bundle\CMSBundle\DependencyInjection\Compiler;

use Symfony\Component\Config\Resource\FileExistenceResource;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TwigLoaderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $twigFilesystemLoaderDefinition = $container->getDefinition('twig.loader.filesystem');

        if (isset($container->getParameter('kernel.bundles')['CMSBundle'])) {
            $reflection = new \ReflectionClass($container->getParameter('kernel.bundles')['CMSBundle']);
            $cmsBundleDir =  dirname($reflection->getFileName());

            // register bundles as Twig namespaces
            foreach ($container->getParameter('kernel.bundles') as $bundle => $class) {
                $dir = $cmsBundleDir.'/Resources/'.$bundle.'/views';
                if (is_dir($dir)) {
                    $name = $bundle;
                    if ('Bundle' === substr($name, -6)) {
                        $name = substr($name, 0, -6);
                    }
                    $twigFilesystemLoaderDefinition->addMethodCall('addCmsAppPath', array($dir, $name));
                }
                $container->addResource(new FileExistenceResource($dir));
            }
        }
    }
}
