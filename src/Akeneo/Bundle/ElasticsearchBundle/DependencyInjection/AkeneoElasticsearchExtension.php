<?php

namespace Akeneo\Bundle\ElasticsearchBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
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

        $container->setParameter('akeneo_elasticsearch.index_configuration.files', $config['configuration_files']);

        // TODO: Check that $config['default_index_name'] and $config['product_and_model_index_name'] are different!
        $container->setParameter('akeneo_elasticsearch.product_index_name', $config['default_index_name']);
        $container->setParameter('akeneo_elasticsearch.product_and_model_index_name', $config['product_and_model_index_name']);

        $container->setParameter('akeneo_elasticsearch.hosts', $config['hosts']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('cursors.yml');
        $loader->load('services.yml');
    }
}
