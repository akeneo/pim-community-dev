<?php

namespace Oro\Bundle\ConfigBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('oro_config');
        $builder->getRootNode()
            ->children()
                ->arrayNode('entity_output')
                ->prototype('array')
                    ->children()
                        ->scalarNode('icon_class')->end()
                        ->scalarNode('name')->end()
                        ->scalarNode('description')->end()
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}
