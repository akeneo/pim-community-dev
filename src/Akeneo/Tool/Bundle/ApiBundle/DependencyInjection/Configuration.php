<?php

namespace Akeneo\Tool\Bundle\ApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder
            ->root('pim_api')
            ->children()
                ->arrayNode('pagination')
                    ->children()
                        ->scalarNode('limit_max')->end()
                        ->scalarNode('limit_by_default')->end()
                    ->end()
                    ->validate()
                        ->ifTrue(function ($v) {
                            return $v['limit_max'] < $v['limit_by_default'];
                        })
                        ->thenInvalid('API configuration: "limit_by_default" cannot be greater than "limit_max.')
                    ->end()
                ->end()
                ->arrayNode('input')
                    ->children()
                        ->scalarNode('buffer_size')->end()
                        ->scalarNode('max_resources_number')->end()
                    ->end()
                ->end()
                ->arrayNode('content_type_negotiator')
                    ->children()
                        ->arrayNode('rules')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('path')->defaultNull()->info('URL path info')->end()
                                    ->scalarNode('host')->defaultNull()->info('URL host name')->end()
                                    ->variableNode('methods')->defaultNull()->info('Method for URL')->end()
                                    ->booleanNode('stop')->defaultFalse()->end()
                                    ->integerNode('priority')->defaultValue(1)->info('Priority order to apply the rule')->end()
                                    ->arrayNode('content_types')
                                        ->prototype('scalar')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
