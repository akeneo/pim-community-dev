<?php

namespace Oro\Bundle\NavigationBundle\DependencyInjection;

use Oro\Bundle\NavigationBundle\Config\Definition\Builder\MenuTreeBuilder;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('oro_menu_config', 'array', new MenuTreeBuilder());

        $node = $rootNode->children();
        $this->setChildren($node);
        $node->end();

        SettingsBuilder::append(
            $rootNode,
            array(
                'maxItems' => array(
                    'value' => 20, // default value, can be overridden in config.yml
                    'type'  => 'scalar',
                ),
                'title_suffix' => array(
                    'value' => '', // default value, can be overridden in config.yml
                    'type'  => 'scalar',
                ),
                'title_delimiter' => array(
                    'value' => '-', // default value, can be overridden in config.yml
                    'type'  => 'scalar',
                ),
                'breadcrumb_menu' => array(
                    'value' => 'application_menu', // default value, can be overridden in config.yml
                    'type'  => 'scalar',
                ),
            )
        );

        return $treeBuilder;
    }

    /**
     * Add children nodes to menu
     *
     * @param $node NodeBuilder
     * @return Configuration
     */
    protected function setChildren($node)
    {
        $node->
        arrayNode('templates')
            ->useAttributeAsKey('templates')
            ->prototype('array')
                ->children()
                    ->scalarNode('template')->end()
                    ->scalarNode('clear_matcher')->end()
                    ->scalarNode('depth')->end()
                    ->scalarNode('allow_safe_labels')->end()
                    ->scalarNode('currentAsLink')->end()
                    ->scalarNode('currentClass')->end()
                    ->scalarNode('ancestorClass')->end()
                    ->scalarNode('firstClass')->end()
                    ->scalarNode('lastClass')->end()
                    ->scalarNode('compressed')->end()
                    ->scalarNode('block')->end()
                    ->scalarNode('rootClass')->end()
                    ->scalarNode('isDropdown')->end()
                ->end()
            ->end()
        ->end()
        ->arrayNode('items')
            ->useAttributeAsKey('id')
            ->prototype('array')
                ->children()
                    ->scalarNode('id')->end()
                    ->scalarNode('name')->end()
                    ->scalarNode('label')->end()
                    ->scalarNode('uri')->end()
                    ->scalarNode('route')->end()
                    ->scalarNode('aclResourceId')->end()
                    ->scalarNode('translateDomain')->end()
                    ->arrayNode('translateParameters')
                        ->useAttributeAsKey('translateParameters')->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('attributes')
                        ->children()
                            ->scalarNode('class')->end()
                            ->scalarNode('id')->end()
                        ->end()
                    ->end()
                    ->arrayNode('linkAttributes')
                        ->children()
                            ->scalarNode('class')->end()
                            ->scalarNode('id')->end()
                            ->scalarNode('target')->end()
                            ->scalarNode('title')->end()
                            ->scalarNode('rel')->end()
                            ->scalarNode('type')->end()
                            ->scalarNode('name')->end()
                            ->scalarNode('type')->end()
                        ->end()
                    ->end()
                    ->arrayNode('childrenAttributes')
                        ->children()
                            ->scalarNode('class')->end()
                            ->scalarNode('id')->end()
                        ->end()
                    ->end()
                    ->arrayNode('labelAttributes')
                        ->children()
                            ->scalarNode('class')->end()
                            ->scalarNode('id')->end()
                        ->end()
                    ->end()
                    ->scalarNode('display')->end()
                    ->scalarNode('displayChildren')->end()
                    ->scalarNode('type')->end()
                    ->arrayNode('routeParameters')
                        ->useAttributeAsKey('routeParameters')->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('extras')
                        ->useAttributeAsKey('extras')->prototype('variable')->end()
                    ->end()
                    ->booleanNode('showNonAuthorized')->end()
                ->end()
            ->end()
        ->end()
        ->arrayNode('tree')
            ->useAttributeAsKey('id')
                ->prototype('array')
                    ->children()
                        ->scalarNode('type')->end()
                        ->arrayNode('extras')
                            ->useAttributeAsKey('extras')->prototype('scalar')->end()
                        ->end()
                        ->menuNode('children')->menuNodeHierarchy()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $this;
    }
}
