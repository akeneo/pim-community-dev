<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Bundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class AkeneoAssetExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('analytics.yml');
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
        $loader->load('converters.yml');
        $loader->load('datagrid/attribute_types.yml');
        $loader->load('datagrid/configurators.yml');
        $loader->load('datagrid/data_sources.yml');
        $loader->load('datagrid/filters.yml');
        $loader->load('datagrid/formatters.yml');
        $loader->load('datagrid/hydrators.yml');
        $loader->load('datagrid/listeners.yml');
        $loader->load('datagrid/selectors.yml');
        $loader->load('datagrid_handlers.yml');
        $loader->load('events.yml');
        $loader->load('factories.yml');
        $loader->load('filters.yml');
        $loader->load('finders.yml');
        $loader->load('forms.yml');
        $loader->load('job_constraints.yml');
        $loader->load('job_defaults.yml');
        $loader->load('managers.yml');
        $loader->load('mass_uploader.yml');
        $loader->load('models.yml');
        $loader->load('normalizers.yml');
        $loader->load('providers.yml');
        $loader->load('queries.yml');
        $loader->load('queries.yml');
        $loader->load('readers.yml');
        $loader->load('removers.yml');
        $loader->load('repositories.yml');
        $loader->load('savers.yml');
        $loader->load('services.yml');
        $loader->load('stream.yml');
        $loader->load('subscribers.yml');
        $loader->load('transformers.yml');
        $loader->load('twig_extension.yml');
        $loader->load('updaters.yml');
        $loader->load('validators.yml');
        $loader->load('versioning/guessers.yml');
        $loader->load('view_elements/asset.yml');
        $loader->load('view_elements/category.yml');
        $loader->load('voters.yml');
        $loader->load('workflow/presenters.yml');
        $loader->load('jobs.yml');
        $loader->load('steps.yml');
        $loader->load('imagine.yml');
    }
}
