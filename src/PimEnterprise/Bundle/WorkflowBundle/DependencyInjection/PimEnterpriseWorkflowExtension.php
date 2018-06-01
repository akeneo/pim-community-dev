<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * PimEnterprise\Bundle\WorkflowBundle\DependencyInjection
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class PimEnterpriseWorkflowExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('analytics.yml');
        $loader->load('appliers.yml');
        $loader->load('builder.yml');
        $loader->load('category_counters.yml');
        $loader->load('configurators.yml');
        $loader->load('connector/array_converters.yml');
        $loader->load('connector/processors.yml');
        $loader->load('connector/readers.yml');
        $loader->load('connector/writers.yml');
        $loader->load('controllers.yml');
        $loader->load('completeness.yml');
        $loader->load('cursors.yml');
        $loader->load('datagrid_listeners.yml');
        $loader->load('entities.yml');
        $loader->load('elasticsearch.yml');
        $loader->load('event_subscribers.yml');
        $loader->load('factories.yml');
        $loader->load('job_constraints.yml');
        $loader->load('job_defaults.yml');
        $loader->load('helpers.yml');
        $loader->load('managers.yml');
        $loader->load('mass_review_actions.yml');
        $loader->load('normalizers.yml');
        $loader->load('presenters.yml');
        $loader->load('product_draft.yml');
        $loader->load('providers.yml');
        $loader->load('publishers.yml');
        $loader->load('query_builders.yml');
        $loader->load('removers.yml');
        $loader->load('repositories.yml');
        $loader->load('savers.yml');
        $loader->load('security/factories.yml');
        $loader->load('security/filters.yml');
        $loader->load('security/queries.yml');
        $loader->load('serializers_indexing.yml');
        $loader->load('twig.yml');
        $loader->load('readers.yml');
        $loader->load('steps.yml');
        $loader->load('jobs.yml');
    }
}
