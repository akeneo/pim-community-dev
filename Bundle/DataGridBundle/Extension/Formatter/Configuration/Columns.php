<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration;

use Oro\Bundle\ImportExportBundle\Exception\InvalidConfigurationException;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Columns implements ConfigurationInterface
{
    /** @var array */
    protected $propertyTypes;

    /** @var array */
    protected $columns;

    /** @var array */
    protected $properties;

    /**
     * @param $propertyTypes
     * @param $columns
     * @param $props
     */
    public function __construct($propertyTypes, $columns, $props)
    {
        $this->propertyTypes = $propertyTypes;
        $this->columns = $columns;
        $this->properties = $props;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();
        $rootNode = $builder->root('columns_and_properties');

        $rootNode
            ->children()
                ->append($this->getColumnTree())
                ->append($this->getPropertyTree())
            ->end();

        return $builder;
    }

    /**
     *
     * @throws InvalidConfigurationException
     * @return NodeDefinition
     */
    public function getPropertyTree()
    {
        $builder = new TreeBuilder();
        $self = $this;

        return $builder->root('properties')
            ->prototype('array')
                ->children()
                    ->scalarNode('type')
                        ->isRequired()
                    ->end()

                ->end()
            ->end();
    }

    public function getUrlPropertyTree()
    {
        $builder = new TreeBuilder();

        return $builder->root('properties')
            ->prototype('array')
                ->children()
                    ->scalarNode('type')
                        ->isRequired()
                    ->end()
                    ->scalarNode('route')
                        ->cannotBeEmpty()
                    ->end()
                    ->arrayNode('params')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     *
     */
    public function getColumnTree()
    {
        $builder = new TreeBuilder();

        return $builder->root('columns')
            ->prototype('array')
                ->children()
                    ->enumNode('type')
                        ->isRequired()
                        ->values($this->propertyTypes)
                    ->end()
                ->end()
            ->end();
    }
}
