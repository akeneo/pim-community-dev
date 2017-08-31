<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Enterprise Enrich extension
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class PimEnterpriseEnrichExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('category_counters.yml');
        $loader->load('connector/processors.yml');
        $loader->load('connector/writers.yml');
        $loader->load('controllers.yml');
        $loader->load('datagrid_listeners.yml');
        $loader->load('event_subscribers.yml');
        $loader->load('filters.yml');
        $loader->load('form_types.yml');
        $loader->load('job_constraints.yml');
        $loader->load('job_defaults.yml');
        $loader->load('jobs.yml');
        $loader->load('imagine.yml');
        $loader->load('mass_actions.yml');
        $loader->load('normalizers.yml');
        $loader->load('parameters.yml');
        $loader->load('providers.yml');
        $loader->load('query_builders.yml');
        $loader->load('repositories.yml');
        $loader->load('steps.yml');
        $loader->load('twig.yml');
        $loader->load('view_elements/attribute.yml');
        $loader->load('view_elements/attribute_group.yml');
        $loader->load('view_elements/category.yml');
        $loader->load('view_elements/channel.yml');
    }
}
