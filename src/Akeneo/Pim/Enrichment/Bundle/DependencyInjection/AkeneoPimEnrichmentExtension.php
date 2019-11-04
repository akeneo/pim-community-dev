<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AkeneoPimEnrichmentExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->loadLocalizationConfiguration($container, $config);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('connector/cleaners.yml');
        $loader->load('connector/processors.yml');
        $loader->load('connector/use_cases.yml');
        $loader->load('api_filters.yml');
        $loader->load('associations.yml');
        $loader->load('builders.yml');
        $loader->load('comparators.yml');
        $loader->load('completeness.yml');
        $loader->load('completeness_queries.yml');
        $loader->load('completeness_mask_generators.yml');
        $loader->load('console.yml');
        $loader->load('context.yml');
        $loader->load('converters.yml');
        $loader->load('cursors.yml');
        $loader->load('elasticsearch.yml');
        $loader->load('enrichment_follow_up.yml');
        $loader->load('entities.yml');
        $loader->load('entity_with_family_variant.yml');
        $loader->load('event_subscribers.yml');
        $loader->load('factories.yml');
        $loader->load('product_value_factories.yml');
        $loader->load('family_variant.yml');
        $loader->load('filters.yml');
        $loader->load('job_constraints.yml');
        $loader->load('job_defaults.yml');
        $loader->load('jobs.yml');
        $loader->load('localization/factories.yml');
        $loader->load('localization/localizers.yml');
        $loader->load('localization/presenters.yml');
        $loader->load('localization/translators.yml');
        $loader->load('localization/validators.yml');
        $loader->load('managers.yml');
        $loader->load('models.yml');
        $loader->load('product_grid_category_tree.yml');
        $loader->load('product_models.yml');
        $loader->load('product_values.yml');
        $loader->load('query_builders.yml');
        $loader->load('reference_data.yml');
        $loader->load('removers.yml');
        $loader->load('repositories.yml');
        $loader->load('resolvers.yml');
        $loader->load('savers.yml');
        $loader->load('queries.yml');
        $loader->load('updaters.yml');
        $loader->load('validators.yml');
        $loader->load('values_fillers.yml');
        $loader->load('versions.yml');
        $loader->load('serializers.yml');
        $loader->load('serializers_indexing.yml');
        $loader->load('serializers_standard.yml');
        $loader->load('serializers_storage.yml');
        $loader->load('serializers_versioning.yml');
        $loader->load('serializers.yml');
        $loader->load('builders.yml');
        $loader->load('controllers.yml');
        $loader->load('renderers.yml');
        $loader->load('form_types.yml');
        $loader->load('normalizers.yml');
        $loader->load('connector.yml');
        $loader->load('array_converter.yml');
        $loader->load('job_parameters.yml');
        $loader->load('processors.yml');
        $loader->load('readers.yml');
        $loader->load('writers.yml');
        $loader->load('steps.yml');
        $loader->load('widgets.yml');
        $loader->load('providers.yml');
        $loader->load('twig.yml');
        $loader->load('filters.yml');
        $loader->load('mass_actions.yml');
        $loader->load('datagrid_actions.yml');
        $loader->load('files.yml');
        $loader->load('category_counters.yml');
        $loader->load('structure_version.yml');
        $loader->load('view_elements/category.yml');
        $loader->load('command.yml');
        $loader->load('cli_command.yml');

        if (!$container->hasParameter('pim_pdf_generator_font')) {
            $container->setParameter('pim_pdf_generator_font', null);
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    protected function loadLocalizationConfiguration(ContainerBuilder $container, array $config)
    {
        $localization = $config['localization'];

        $decimalSeparators = [];
        foreach ($localization['decimal_separators'] as $decimalSeparator) {
            $decimalSeparators[$decimalSeparator['value']] = $decimalSeparator['label'];
        }
        $container->setParameter('pim_catalog.localization.decimal_separators', $decimalSeparators);

        $dateFormats = [];
        foreach ($localization['date_formats'] as $dateFormat) {
            $dateFormats[$dateFormat['value']] = $dateFormat['label'];
        }
        $container->setParameter('pim_catalog.localization.date_formats', $dateFormats);
    }
}
