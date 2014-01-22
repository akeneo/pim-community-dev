<?php

namespace Oro\Bundle\FlexibleEntityBundle\Grid;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    const FLEXIBLE_ATTRIBUTES_KEY = 'flexible_attributes';

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        $builder->root(self::FLEXIBLE_ATTRIBUTES_KEY)
            ->prototype('array')
                ->ignoreExtraKeys()
                    ->children()
                        ->booleanNode('filterable')->end()
                        ->booleanNode('sortable')->end()
                        ->booleanNode('filter_enabled')->end()
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}
