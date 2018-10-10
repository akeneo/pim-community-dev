<?php

namespace Pim\Bundle\EnrichBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Enrich extension
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimEnrichExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $this->processConfiguration($configuration, $configs);

        $container->setParameter(
            'pim_enrich_max_products_category_removal',
            $configs[0]['max_products_category_removal']
        );

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('colors.yml');
        $loader->load('controllers.yml');
        $loader->load('datagrid_listeners.yml');
        $loader->load('forms.yml');
        $loader->load('handlers.yml');
        $loader->load('jobs.yml');
        $loader->load('providers.yml');
        $loader->load('queries.yml');
        $loader->load('query_builder.yml');
        $loader->load('steps.yml');
        $loader->load('structure_version.yml');
        $loader->load('view_elements/attribute.yml');
        $loader->load('view_elements/category.yml');
        $loader->load('view_elements/group_type.yml');
        $loader->load('view_elements/mass_edit.yml');
    }
}
