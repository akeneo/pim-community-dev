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

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('localizers.yml');
        $loader->load('providers.yml');
    }
}
