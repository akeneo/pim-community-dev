<?php

namespace Oro\Bundle\FlexibleEntityBundle\Grid;

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

        $builder->root('flexible_attributes')
            ->prototype('array')
                ->ignoreExtraKeys()
                    ->children()
                        ->booleanNode('filterable')->end()
                        ->booleanNode('sortable')->end()
                        ->booleanNode('filter_show')->end()
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}
