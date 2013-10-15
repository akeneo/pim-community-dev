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
            ->children()
                ->arrayNode('columns')
                    ->prototype('array')
                        ->ignoreExtraKeys()
                        ->children()
                            ->scalarNode('data_name')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('default')
                        ->prototype('enum')
                            ->values(array(OrmSorterExtension::DIRECTION_DESC, OrmSorterExtension::DIRECTION_ASC))
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}
