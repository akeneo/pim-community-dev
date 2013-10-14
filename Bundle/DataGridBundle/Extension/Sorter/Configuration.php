<?php

namespace Oro\Bundle\DataGridBundle\Extension\Sorter;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        $builder->root('sorters')
            ->prototype('array')
                ->ignoreExtraKeys()
                ->children()
                    ->variableNode('label')->end()
                    ->arrayNode('id')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}
