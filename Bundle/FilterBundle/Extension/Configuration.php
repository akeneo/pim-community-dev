<?php

namespace Oro\Bundle\FilterBundle\Extension;

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
                            ->scalarNode('data_name')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('default')
                        ->prototype('variable')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}
