<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter;

use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    const DEFAULT_TYPE = 'field';
    const DEFAULT_FRONTEND_TYPE = PropertyInterface::TYPE_STRING;

    const TYPE_KEY = 'type';
    const COLUMNS_KEY = 'columns';
    const OTHER_COLUMNS_KEY = 'other_columns';
    const PROPERTIES_KEY = 'properties';

    /** @var array */
    protected $types;

    protected $root;

    /**
     * @param        $types
     * @param string $root
     */
    public function __construct($types, $root)
    {
        $this->types = $types;
        $this->root = $root;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        $builder->root($this->root)
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->ignoreExtraKeys()
                ->children()
                    ->scalarNode(self::TYPE_KEY)
                        ->defaultValue(self::DEFAULT_TYPE)
                        ->validate()
                        ->ifNotInArray($this->types)
                            ->thenInvalid('Invalid property type "%s"')
                        ->end()
                    ->end()
                    // just validate type if node exist
                    ->scalarNode(PropertyInterface::FRONTEND_TYPE_KEY)->defaultValue(self::DEFAULT_FRONTEND_TYPE)->end()
                    ->scalarNode('label')->end()
                    ->booleanNode('editable')->defaultFalse()->end()
                    ->booleanNode('renderable')->defaultTrue()->end()
                ->end()
            ->end();

        return $builder;
    }
}
