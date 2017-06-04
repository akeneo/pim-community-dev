<?php

namespace Pim\Bundle\CatalogBundle\DependencyInjection;

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
class PimCatalogExtension extends Extension
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
        $loader->load('attribute_types.yml');
        $loader->load('builders.yml');
        $loader->load('collectors.yml');
        $loader->load('comparators.yml');
        $loader->load('completeness_checkers.yml');
        $loader->load('console.yml');
        $loader->load('context.yml');
        $loader->load('doctrine.yml');
        $loader->load('entities.yml');
        $loader->load('event_subscribers.yml');
        $loader->load('factories.yml');
        $loader->load('filters.yml');
        $loader->load('helpers.yml');
        $loader->load('localization/factories.yml');
        $loader->load('localization/localizers.yml');
        $loader->load('localization/presenters.yml');
        $loader->load('localization/translators.yml');
        $loader->load('localization/validators.yml');
        $loader->load('managers.yml');
        $loader->load('models.yml');
        $loader->load('query_builders.yml');
        $loader->load('removers.yml');
        $loader->load('repositories.yml');
        $loader->load('resolvers.yml');
        $loader->load('savers.yml');
        $loader->load('updaters.yml');
        $loader->load('validators.yml');
        $loader->load('versions.yml');
        $loader->load('serializers.yml');
        $loader->load('serializers_standard.yml');
        $loader->load('serializers_storage.yml');
        $loader->load('converters.yml');

        $this->loadStorageDriver($container);
    }

    /**
     * Load the mapping for product and product storage
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
