<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Symfony\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @author    Weasels
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AkeneoCategoryExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('connector/flat_translators.yml');
        $loader->load('connector/use_cases.yml');
        $loader->load('entities.yml');
        $loader->load('models.yml');
        $loader->load('controllers.yml');
        $loader->load('product_grid_category_tree.yml');
        $loader->load('category_counters.yml');
        $loader->load('repositories.yml');
        $loader->load('array_converter.yml');
        $loader->load('documentation.yml');
        $loader->load('event_subscribers.yml');
        $loader->load('factories.yml');
        $loader->load('form_types.yml');
        $loader->load('forms.yml');
        $loader->load('job_constraints.yml');
        $loader->load('job_defaults.yml');
        $loader->load('jobs.yml');
        $loader->load('normalizers.yml');
        $loader->load('providers.yml');
        $loader->load('queries.yml');
        $loader->load('query_builders.yml');
        $loader->load('readers.yml');
        $loader->load('removers.yml');
        $loader->load('savers.yml');
        $loader->load('scope_mappers.yml');
        $loader->load('serializers_standard.yml');
        $loader->load('serializers_versioning.yml');
        $loader->load('steps.yml');
    }
}
