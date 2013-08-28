<?php

namespace Pim\Bundle\ProductBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Yaml;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class PimProductExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        // process configuration to validation and merge
        $currencyConfig = Yaml::parse(realpath(__DIR__ .'/../Resources/config/pim_currencies.yml'));
        $this->processConfiguration(new CurrencyConfiguration(), $currencyConfig);
        $container->setParameter('pim_product.currencies', $currencyConfig);

        $localeConfig = Yaml::parse(realpath(__DIR__.'/../Resources/config/pim_locales.yml'));
        $this->processConfiguration(new LocaleConfiguration(), $localeConfig);
        $container->setParameter('pim_product.locales', $localeConfig);

        $config = $this->processConfiguration(new Configuration, $configs);

        $container->setParameter(
            'pim_product.imported_product_data_transformer',
            $config['imported_product_data_transformer']
        );

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('parameters.yml');
        $loader->load('forms.yml');
        $loader->load('form_types.yml');
        $loader->load('handlers.yml');
        $loader->load('managers.yml');
        $loader->load('services.yml');
        $loader->load('datagrid.yml');
        $loader->load('attribute_types.yml');
        $loader->load('attribute_constraint_guessers.yml');

        if ($config['record_mails']) {
            $loader->load('mail_recorder.yml');
        }
    }
}
