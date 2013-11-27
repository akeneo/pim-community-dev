<?php

namespace Oro\Bundle\FilterBundle\Grid\Extension;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use Oro\Bundle\FilterBundle\Filter\Orm\FilterUtility;

class Configuration implements ConfigurationInterface
{
    const FILTERS_PATH         = '[filters]';
    const COLUMNS_PATH         = '[filters][columns]';
    const DEFAULT_FILTERS_PATH = '[filters][default]';

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
                            ->scalarNode(FilterUtility::TYPE_KEY)
                                ->isRequired()
                                ->validate()
                                ->ifNotInArray($this->types)
                                    ->thenInvalid('Invalid filter type "%s"')
                                ->end()
                            ->end()
                            ->scalarNode(FilterUtility::DATA_NAME_KEY)->isRequired()->end()
                            ->enumNode('filter_condition')
                                ->values(array(FilterUtility::CONDITION_AND, FilterUtility::CONDITION_OR))
                            ->end()
                            ->booleanNode('filter_by_having')->end()
                            ->booleanNode(FilterUtility::ENABLED_KEY)->end()
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
