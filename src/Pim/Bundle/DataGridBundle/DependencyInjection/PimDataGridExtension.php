<?php

namespace Pim\Bundle\DataGridBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

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
        $loader->load('controllers.yml');
        $loader->load('data_sources.yml');
        $loader->load('registry.yml');
        $loader->load('formatters.yml');
        $loader->load('selectors.yml');
        $loader->load('sorters.yml');
        $loader->load('pagers.yml');
        $loader->load('extensions.yml');
        $loader->load('actions.yml');
        $loader->load('hydrators.yml');
        $loader->load('mass_actions.yml');
        $loader->load('event_listeners.yml');
        $loader->load('attribute_types.yml');
        $loader->load('form_types.yml');
        $loader->load('managers.yml');
        $loader->load('repositories.yml');
        $loader->load('configurators.yml');
        $loader->load('adapters.yml');

        $storageDriver = $container->getParameter('pim_catalog_product_storage_driver');
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load(sprintf('storage_driver/%s.yml', $storageDriver));
    }
}
