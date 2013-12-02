<?php

namespace Oro\Bundle\SearchBundle\DependencyInjection;

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
     * Bundle configuration structure
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('oro_search');

        $rootNode->children()
            ->scalarNode('engine')
                ->cannotBeEmpty()
                ->defaultValue('orm')
            ->end()
            ->booleanNode('log_queries')
                ->defaultFalse()
            ->end()
            ->arrayNode('engine_orm')
                ->prototype('scalar')->end()
            ->end()
            ->booleanNode('realtime_update')
                ->defaultTrue()
            ->end()
            ->arrayNode('config_paths')
                ->prototype('scalar')->end()
            ->end()
            ->scalarNode('item_container_template')
                ->defaultValue('OroSearchBundle:Datagrid:itemContainer.html.twig')
            ->end()
            ->arrayNode('entities_config')
                ->prototype('array')
                ->children()
                    ->scalarNode('alias')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('search_template')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->arrayNode('fields')
                        ->prototype('array')
                        ->children()
                            ->scalarNode('name')->end()
                            ->scalarNode('relation_type')->end()
                            ->scalarNode('target_type')->end()
                            ->arrayNode('target_fields')
                                ->prototype('scalar')->end()
                                ->end()
                            ->scalarNode('getter')->end()
                            ->scalarNode('relation_class')->end()
                            ->arrayNode('relation_fields')
                                ->prototype('array')
                                ->children()
                                    ->scalarNode('name')->end()
                                    ->scalarNode('target_type')->end()
                                    ->arrayNode('target_fields')
                                        ->prototype('scalar')->end()
                                        ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end();

        return $treeBuilder;
    }
}
