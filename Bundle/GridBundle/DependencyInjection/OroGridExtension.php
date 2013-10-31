<?php

namespace Oro\Bundle\GridBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;

class OroGridExtension extends Extension
{
    const PARAMETER_TRANSLATION_DOMAIN = 'oro_grid.translation.translation_domain';

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter(self::PARAMETER_TRANSLATION_DOMAIN, $config[Configuration::TRANSLATION_DOMAIN_NODE]);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('orm_filter_types.yml');
        $loader->load('orm_sorter_types.yml');
        $loader->load('action_types.yml');
    }
}
