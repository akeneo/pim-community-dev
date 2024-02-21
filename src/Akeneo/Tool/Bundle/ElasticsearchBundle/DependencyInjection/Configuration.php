<?php

namespace Akeneo\Tool\Bundle\ElasticsearchBundle\DependencyInjection;

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
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('akeneo_elasticsearch');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('hosts')
                    ->prototype('scalar')->defaultValue(['localhost:9200'])->end()
                    ->isRequired()->requiresAtLeastOneElement()
                    ->beforeNormalization()
                        ->ifString()
                        ->then(function ($value) {
                            return [$value];
                        })
                    ->end()
                    ->info('Inline hosts of the Elasticsearch nodes. See https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_configuration.html#_inline_host_configuration. If you have a single host, you can use a string here. Otherwise, use an array.')
                ->end()
                ->integerNode('max_chunk_size')
                    ->isRequired()
                    ->info('Determines the maximum size of an individual bulk request in characters')
                ->end()
                ->integerNode('max_expected_indexation_latency_in_milliseconds')
                    ->defaultValue(1000)
                    ->info('Latency between api call and document availability for search in milliseconds')
                ->end()
                ->integerNode('max_number_of_retries')
                    ->defaultValue(3)
                    ->info('Number of retries after Elasticsearch technical errors')
                ->end()
                ->arrayNode('indexes')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('index_name')
                                ->isRequired()->cannotBeEmpty()
                                ->info('An index name.')
                            ->end()
                            ->arrayNode('configuration_files')
                                ->isRequired()
                                ->requiresAtLeastOneElement()
                                ->prototype('scalar')->isRequired()->cannotBeEmpty()->end()
                                ->info('Paths of the YAML files to configure the index. See https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-create-index.html and src/Akeneo/Bundle/ElasticsearchBundle/IndexConfiguration/IndexConfiguration.php.')
                            ->end()
                            ->scalarNode('service_name')
                                ->isRequired()->cannotBeEmpty()
                                ->info('Name of the symfony service for this client that will be automatically registered in the symfony container.')
                            ->end()
                            ->scalarNode('id_prefix')
                                ->defaultValue('')
                                ->info('Prefix all document ids')
                            ->end()
                            ->scalarNode('activate_dual_indexation_with_service')
                                ->info('Activate dual indexation using the specified service as value')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
