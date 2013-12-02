<?php

namespace Oro\Bundle\QueryDesignerBundle\QueryDesigner;

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
            ->prototype('array')
                ->ignoreExtraKeys()
                ->children()
                    ->arrayNode('applicable')
                        ->requiresAtLeastOneElement()
                        ->prototype('array')
                            ->children()
                                ->scalarNode('type')    // field type
                                    ->cannotBeEmpty()
                                ->end()
                                ->scalarNode('entity')  // entity name
                                    ->cannotBeEmpty()
                                ->end()
                                ->scalarNode('field')   // field name
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->scalarNode('type')
                        ->isRequired()
                        ->validate()
                        ->ifNotInArray($this->types)
                            ->thenInvalid('Invalid filter type "%s"')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}
