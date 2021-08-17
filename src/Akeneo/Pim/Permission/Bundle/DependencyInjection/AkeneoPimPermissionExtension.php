<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AkeneoPimPermissionExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('product_grid_category_tree.yml');
        $loader->load('api.yml');
        $loader->load('analytics.yml');
        $loader->load('array_converters.yml');
        $loader->load('checkers.yml');
        $loader->load('cli_commands.yml');
        $loader->load('controllers.yml');
        $loader->load('entities.yml');
        $loader->load('event_subscribers.yml');
        $loader->load('factories.yml');
        $loader->load('form.yml');
        $loader->load('guessers.yml');
        $loader->load('managers.yml');
        $loader->load('mergers.yml');
        $loader->load('processors.yml');
        $loader->load('queries.yml');
        $loader->load('removers.yml');
        $loader->load('readers.yml');
        $loader->load('repositories.yml');
        $loader->load('savers.yml');
        $loader->load('twig.yml');
        $loader->load('updaters.yml');
        $loader->load('voters.yml');
        $loader->load('normalizers.yml');
        $loader->load('writers.yml');
        $loader->load('filters.yml');
        $loader->load('query_builder.yml');
        $loader->load('query_builders.yml');
        $loader->load('datagrid.yml');
        $loader->load('mass_edit.yml');
        $loader->load('renderers.yml');
        $loader->load('context.yml');
        $loader->load('view_elements/attribute_group.yml');
        $loader->load('view_elements/category.yml');
        $loader->load('view_elements/attribute.yml');
        $loader->load('steps.yml');
        $loader->load('job_constraints.yml');
        $loader->load('job_defaults.yml');
        $loader->load('jobs.yml');
        $loader->load('validators.yml');
    }
}
