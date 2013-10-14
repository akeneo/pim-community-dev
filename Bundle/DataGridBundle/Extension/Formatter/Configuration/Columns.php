<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Columns implements ConfigurationInterface
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

        $builder->root('columns_and_properties')
            ->prototype('array')
                ->children()
                    ->scalarNode('type')
                        ->isRequired()
                        ->validate()
                        ->ifNotInArray($this->types)
                            ->thenInvalid('Invalid property type "%s"')
                        ->end()
                    ->end()
                    ->scalarNode('label')->end()
                    ->scalarNode('route')->end()
                    ->arrayNode('params')
                        ->prototype('scalar')
                        ->end()
                    ->end()
                    ->scalarNode('sortable')->end()
                    ->arrayNode('options')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('type')->end()
                                ->arrayNode('params')
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}
