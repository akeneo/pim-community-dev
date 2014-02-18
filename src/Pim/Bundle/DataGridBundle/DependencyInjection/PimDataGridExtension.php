<?php

namespace Pim\Bundle\DataGridBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class PimDataGridExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('data_sources.yml');
        $loader->load('registry.yml');
        $loader->load('formatters.yml');
        $loader->load('grid_extensions.yml');
        $loader->load('grid_actions.yml');
        $loader->load('grid_listeners.yml');
        $loader->load('grid_attribute_types.yml');
    }
}
