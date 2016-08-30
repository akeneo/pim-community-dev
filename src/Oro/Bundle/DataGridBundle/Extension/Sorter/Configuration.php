<?php

namespace Oro\Bundle\DataGridBundle\Extension\Sorter;

use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    const SORTERS_PATH = '[sorters]';
    const COLUMNS_PATH = '[sorters][columns]';
    const MULTISORT_PATH = '[sorters][multiple_sorting]';
    const DEFAULT_SORTERS_PATH = '[sorters][default]';

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
                        ->children()
                            ->scalarNode(PropertyInterface::DATA_NAME_KEY)->isRequired()->end()
                            ->variableNode('apply_callback')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('default')
                    ->prototype('enum')
                        ->values([OrmSorterExtension::DIRECTION_DESC, OrmSorterExtension::DIRECTION_ASC])->end()
                    ->end()
                    ->booleanNode('multiple_sorting')->end()
                ->end()
            ->end();

        return $builder;
    }
}
