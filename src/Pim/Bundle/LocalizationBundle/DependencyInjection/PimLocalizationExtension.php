<?php

namespace Pim\Bundle\LocalizationBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class PimLocalizationExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $decimalSeparators = [];
        foreach ($config['decimal_separators'] as $decimalSeparator) {
            $decimalSeparators[$decimalSeparator['value']] = $decimalSeparator['label'];
        }
        $container->setParameter('pim_localization.decimal_separators', $decimalSeparators);

        $dateFormats = [];
        foreach ($config['date_formats'] as $dateFormat) {
            $dateFormats[$dateFormat['value']] = $dateFormat['label'];
        }
        $container->setParameter('pim_localization.date_formats', $dateFormats);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('controllers.yml');
        $loader->load('converters.yml');
        $loader->load('denormalizers.yml');
        $loader->load('localizers.yml');
        $loader->load('normalizers.yml');
        $loader->load('providers.yml');
        $loader->load('resolvers.yml');
        $loader->load('services.yml');
        $loader->load('twig.yml');
    }
}
