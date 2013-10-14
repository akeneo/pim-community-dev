<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter;

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

        $builder->root('columns_and_properties')
            ->prototype('array')
                ->ignoreExtraKeys()
                ->children()
                    ->scalarNode('type')
                        ->isRequired()
                        ->validate()
                        ->ifNotInArray($this->types)
                            ->thenInvalid('Invalid property type "%s"')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}
