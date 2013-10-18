<?php

namespace Oro\Bundle\FilterBundle\Extension;

use Oro\Bundle\FilterBundle\Extension\Orm\FilterInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /** @var array */
    protected $types;

    /**
     * @param $types
     */
    public function __construct($types)
    {
        $this->types = $types;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        $builder->root('filters')
            ->children()
                ->arrayNode('columns')
                    ->prototype('array')
                        ->ignoreExtraKeys()
                        ->children()
                            ->scalarNode('type')
                                ->isRequired()
                                ->validate()
                                ->ifNotInArray($this->types)
                                    ->thenInvalid('Invalid filter type "%s"')
                                ->end()
                            ->end()
                            ->scalarNode('data_name')->isRequired()->end()
                            ->enumNode('filter_condition')
                                ->values(array(FilterInterface::CONDITION_AND, FilterInterface::CONDITION_OR))
                            ->end()
                            ->booleanNode('filter_by_having')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('default')
                        ->prototype('array')
                            ->prototype('variable')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}
