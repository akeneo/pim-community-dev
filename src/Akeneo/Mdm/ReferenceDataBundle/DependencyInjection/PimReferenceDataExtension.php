<?php

namespace Pim\Bundle\ReferenceDataBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimReferenceDataExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $this->processConfiguration($configuration, $configs);

        $container->setParameter('pim_reference_data.configurations', $configs[0]);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('attribute_types.yml');
        $loader->load('controllers.yml');
        $loader->load('datagrid/filters.yml');
        $loader->load('datagrid/normalizers.yml');
        $loader->load('datagrid/query_builders.yml');
        $loader->load('datagrid/sorters.yml');
        $loader->load('factories.yml');
        $loader->load('models.yml');
        $loader->load('product_values.yml');
        $loader->load('providers.yml');
        $loader->load('query_builders.yml');
        $loader->load('serializers.yml');
        $loader->load('serializers_indexing.yml');
        $loader->load('services.yml');
        $loader->load('updaters.yml');
    }
}
