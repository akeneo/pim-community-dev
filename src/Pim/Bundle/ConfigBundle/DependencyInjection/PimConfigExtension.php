<?php

namespace Pim\Bundle\ConfigBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Yaml\Yaml;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class PimConfigExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        // process configuration to validation and merge
        $currencyConfig = Yaml::parse(realpath(__DIR__ .'/../Resources/config/pim_currencies.yml'));
        $config = $this->processConfiguration(new CurrencyConfiguration(), $currencyConfig);
        $container->setParameter('pim_config.currencies', $currencyConfig);

        $localeConfig = Yaml::parse(realpath(__DIR__.'/../Resources/config/pim_locales.yml'));
        $config = $this->processConfiguration(new LocaleConfiguration(), $localeConfig);
        $container->setParameter('pim_config.locales', $localeConfig);

        // load services
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('parameters.yml');
        $loader->load('forms.yml');
        $loader->load('form_types.yml');
        $loader->load('handlers.yml');
        $loader->load('managers.yml');
        $loader->load('services.yml');
        $loader->load('datagrid.yml');
    }
}
