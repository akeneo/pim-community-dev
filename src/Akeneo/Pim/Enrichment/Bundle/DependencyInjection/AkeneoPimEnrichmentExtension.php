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
        $loader->import('*.yml', 'glob');
        $loader->import('localization/*.yml', 'glob');

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
