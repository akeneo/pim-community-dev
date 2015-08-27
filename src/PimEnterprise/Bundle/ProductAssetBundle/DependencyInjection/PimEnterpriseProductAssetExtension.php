<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class PimEnterpriseProductAssetExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('array_converters.yml');
        $loader->load('attribute_types.yml');
        $loader->load('builders.yml');
        $loader->load('category_counters.yml');
        $loader->load('connector/array_converters.yml');
        $loader->load('connector/processors.yml');
        $loader->load('connector/readers.yml');
        $loader->load('connector/writers.yml');
        $loader->load('context.yml');
        $loader->load('controllers.yml');
        $loader->load('data_sources.yml');
        $loader->load('datagrid/attribute_types.yml');
        $loader->load('datagrid/filters.yml');
        $loader->load('datagrid/formatters.yml');
        $loader->load('datagrid/selectors.yml');
        $loader->load('events.yml');
        $loader->load('factories.yml');
        $loader->load('filters.yml');
        $loader->load('finders.yml');
        $loader->load('formatters.yml');
        $loader->load('forms.yml');
        $loader->load('managers.yml');
        $loader->load('mass-uploader.yml');
        $loader->load('models.yml');
        $loader->load('normalizers.yml');
        $loader->load('processors.yml');
        $loader->load('providers.yml');
        $loader->load('query_builders.yml');
        $loader->load('readers.yml');
        $loader->load('removers.yml');
        $loader->load('repositories.yml');
        $loader->load('savers.yml');
        $loader->load('selectors.yml');
        $loader->load('services.yml');
        $loader->load('steps.yml');
        $loader->load('subscribers.yml');
        $loader->load('twig_extension.yml');
        $loader->load('updaters.yml');
        $loader->load('validators.yml');
        $loader->load('view_elements.yml');
        $loader->load('voters.yml');
        $loader->load('writers.yml');

        $this->loadStorageDriver($container);
    }

    /**
     * Load config for specific storage
     *
     * @param ContainerBuilder $container
     */
    protected function loadStorageDriver(ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $storageDriver = $container->getParameter('pim_catalog_product_storage_driver');
        $storageConfig = sprintf('storage_driver/%s.yml', $storageDriver);
        if (file_exists(__DIR__ . '/../Resources/config/' . $storageConfig)) {
            $loader->load($storageConfig);
        }
    }
}
