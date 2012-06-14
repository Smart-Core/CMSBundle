<?php

namespace SmartCore\Bundle\EngineBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class SmartCoreEngineExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();        
        $config = $this->processConfiguration($configuration, $configs);
        
        $container->setParameter($this->getAlias() . '.dir_sites', $config['dir_sites']);
        $container->setParameter($this->getAlias() . '.storage', $config['storage']);

        if ($container->hasParameter('liip_theme.file_locator.class')) {
            $container->setParameter('liip_theme.file_locator.class', 'SmartCore\Bundle\EngineBundle\Locator\MultisitesFileLocator');
            $container->setAlias($this->getAlias() . '.active_theme', 'liip_theme.active_theme');
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}