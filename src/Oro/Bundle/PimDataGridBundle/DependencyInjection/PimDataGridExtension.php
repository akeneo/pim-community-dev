<?php

namespace Oro\Bundle\PimDataGridBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
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
        $loader->load('actions.yml');
        $loader->load('adapters.yml');
        $loader->load('attribute_types.yml');
        $loader->load('configurators.yml');
        $loader->load('controllers.yml');
        $loader->load('data_sources.yml');
        $loader->load('entities.yml');
        $loader->load('event_listeners.yml');
        $loader->load('extensions.yml');
        $loader->load('factories.yml');
        $loader->load('form_types.yml');
        $loader->load('formatters.yml');
        $loader->load('hydrators.yml');
        $loader->load('managers.yml');
        $loader->load('mass_actions.yml');
        $loader->load('normalizers.yml');
        $loader->load('pagers.yml');
        $loader->load('registry.yml');
        $loader->load('removers.yml');
        $loader->load('repositories.yml');
        $loader->load('savers.yml');
        $loader->load('sorters.yml');
        $loader->load('subscribers.yml');
        $loader->load('twig.yml');
        $loader->load('updaters.yml');
        $loader->load('datagrid_listeners.yml');
        $loader->load('queries.yml');
    }
}
