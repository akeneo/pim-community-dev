<?php

namespace Oro\Bundle\FormBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('oro_form');
        $rootNode
            ->children()
                ->arrayNode('autocomplete_entities')
                ->useAttributeAsKey('autocomplete_entities')
                ->prototype('array')
                    ->children()
                        ->scalarNode('class')
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('resource')->end()
                        ->scalarNode('property')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
