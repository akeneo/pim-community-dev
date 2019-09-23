<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class AkeneoVersioningExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('builders.yml');
        $loader->load('entities.yml');
        $loader->load('event_subscribers.yml');
        $loader->load('factories.yml');
        $loader->load('guessers.yml');
        $loader->load('managers.yml');
        $loader->load('purgers.yml');
        $loader->load('removers.yml');
        $loader->load('repositories.yml');
        $loader->load('savers.yml');
        $loader->load('cli_commands.yml');

        $file = __DIR__.'/../Resources/config/pim_versioning_entities.yml';
        $entities = Yaml::parse(file_get_contents(realpath($file)));
        $container->setParameter('pim_versioning.versionable_entities', $entities['versionable']);

        $this->loadSerializerConfig($configs, $container);
    }

    /**
     * Load serializer related configuration
     *
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    protected function loadSerializerConfig(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/serializer'));
        $loader->load('serializer.yml');
    }
}
