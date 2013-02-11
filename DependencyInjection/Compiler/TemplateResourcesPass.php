<?php

namespace SmartCore\Bundle\EngineBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Bundle\AsseticBundle\DependencyInjection\DirectoryResourceDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Exception\LogicException;

use Liip\ThemeBundle\DependencyInjection\Compiler\TemplateResourcesPass as BaseTemplateResourcesPass;

/**
 * This pass adds directory resources to scan for assetic assets.
 */
class TemplateResourcesPass extends BaseTemplateResourcesPass
{
    // @todo убрать
    protected $site_id = '/1';
    
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('assetic.asset_manager')) {
            return;
        }

        $engines = $container->getParameter('templating.engines');
        
        // bundle and kernel resources
        $bundles = $container->getParameter('kernel.bundles');
        $asseticBundles = $container->getParameterBag()->resolveValue($container->getParameter('assetic.bundles'));

        foreach ($asseticBundles as $bundleName) {
            $rc = new \ReflectionClass($bundles[$bundleName]);            
            foreach ($engines as $engine) {
                $this->setBundleDirectoryResources($container, $engine, dirname($rc->getFileName()), $bundleName);
            }
        }

        foreach ($engines as $engine) {
            $this->setAppDirectoryResources($container, $engine);
        }
    }

    protected function setBundleDirectoryResources(ContainerBuilder $container, $engine, $bundleDirName, $bundleName)
    {
        if (!$container->hasDefinition('assetic.'.$engine.'_directory_resource.'.$bundleName)) {
            throw new LogicException('The LiipThemeBundle must be registered after the AsseticBundle in the application Kernel.');
        }

        $resources = $container->getDefinition('assetic.'.$engine.'_directory_resource.'.$bundleName)->getArgument(0);
        
        $themes = $container->getParameter('liip_theme.themes');
        
        foreach ($themes as $theme) {
            $resources[] = new DirectoryResourceDefinition(
                $bundleName,
                $engine,
                array(
                    $container->getParameter('kernel.root_dir') . $this->site_id . '/Resources/'.$bundleName.'/themes/'.$theme,
                    $bundleDirName . $this->site_id . '/Resources/themes/'.$theme,
                )
            );
        }

        $container->getDefinition('assetic.'.$engine.'_directory_resource.'.$bundleName)->replaceArgument(0, $resources);
    }

    protected function setAppDirectoryResources(ContainerBuilder $container, $engine)
    {
        if (!$container->hasDefinition('assetic.'.$engine.'_directory_resource.kernel')) {
            throw new LogicException('The LiipThemeBundle must be registered after the AsseticBundle in the application Kernel.');
        }

        $themes = $container->getParameter('liip_theme.themes');
        
        foreach ($themes as $key => $theme) {
            $themes[$key] = $container->getParameter('kernel.root_dir') . $this->site_id . '/Resources/themes/'.$theme;
        }
        $themes[] = $container->getParameter('kernel.root_dir') . $this->site_id . '/Resources/views';

//        sc_dump($container->getDefinition('assetic.'.$engine.'_directory_resource.kernel')); exit;

        $container->setDefinition(
            'assetic.'.$engine.'_directory_resource.kernel',
            new DirectoryResourceDefinition('', $engine, $themes)
        );
    }
}
