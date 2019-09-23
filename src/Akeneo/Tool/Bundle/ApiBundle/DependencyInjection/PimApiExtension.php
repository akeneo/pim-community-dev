<?php

namespace Akeneo\Tool\Bundle\ApiBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class PimApiExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('pim_api.configuration', $config);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('controllers.yml');
        $loader->load('checkers.yml');
        $loader->load('converters.yml');
        $loader->load('event_subscribers.yml');
        $loader->load('hateoas.yml');
        $loader->load('negotiators.yml');
        $loader->load('normalizers.yml');
        $loader->load('repositories.yml');
        $loader->load('security.yml');
        $loader->load('serializers.yml');
        $loader->load('stream.yml');
        $loader->load('cli_commands.yml');
    }
}
