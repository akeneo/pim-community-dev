<?php

namespace Akeneo\Bundle\ElasticsearchBundle\DependencyInjection;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Bundle\ElasticsearchBundle\IndexConfiguration\Loader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class AkeneoElasticsearchExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        foreach ($config['indexes'] as $index) {
            $configurationLoaderServiceName = sprintf(
                'akeneo_elasticsearch.index_configuration.%s.files',
                $index['index_name']
            );
            $container->register($configurationLoaderServiceName, Loader::class)
                ->setArguments([$index['configuration_files']]);

            $container->register($index['service_name'], Client::class)
                ->setArguments([
                    new Reference('akeneo_elasticsearch.client_builder'),
                    new Reference($configurationLoaderServiceName),
                    $config['hosts'],
                    $index['index_name']
                ]);
        }

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('cursors.yml');
        $loader->load('services.yml');
    }
}
