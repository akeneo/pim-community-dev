<?php

namespace Oro\Bundle\DataGridBundle\Extension\Sorter;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;

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
                            ->scalarNode(PropertyInterface::DATA_NAME_KEY)->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('default')
                    ->prototype('enum')
                        ->values([OrmSorterExtension::DIRECTION_DESC, OrmSorterExtension::DIRECTION_ASC])
                        ->end()
                    ->end()
                    ->booleanNode('multiple_sorting')->end()
                ->end()
            ->end();

        return $builder;
    }
}
