<?php

namespace Akeneo\Bundle\ElasticsearchBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('akeneo_elasticsearch');

        $rootNode
            ->children()
                ->scalarNode('index_name')
                    ->isRequired()->cannotBeEmpty()
                    ->info('The index name.')
                ->end()
                ->arrayNode('hosts')
                    ->prototype('scalar')->defaultValue(['localhost:9200'])->end()
                    ->isRequired()->cannotBeEmpty()
                    ->beforeNormalization()
                        ->ifString()
                        ->then(function ($value) {
                            return [$value];
                        })
                    ->end()
                    ->info('Inline hosts of the Elasticsearch nodes. See https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_configuration.html#_inline_host_configuration. If you have a single host, you can use a string here. Otherwise, use an array.')
                ->end()
                ->arrayNode('configuration_files')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->prototype('scalar')->isRequired()->cannotBeEmpty()->end()
                    ->info('Paths of the YAML files to configure the index. See https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-create-index.html and src/Akeneo/Bundle/ElasticsearchBundle/IndexConfiguration/IndexConfiguration.php.')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
