<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration;

use Oro\Bundle\ImportExportBundle\Exception\InvalidConfigurationException;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Columns implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();
        $rootNode = $builder->root('columns_and_properties');

        $rootNode
            ->children()
                ->arrayNode('columns')
                    ->children()
                    ->end()
                ->end()
                ->arrayNode('properties')
                    ->children()
                        ->arrayNode('update_link')
                            ->append($this->getLinkProperty('update_link'))
                        ->end()
                        ->arrayNode('delete_link')
                            ->append($this->getLinkProperty('delete_link'))
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $builder;
    }

    /**
     * @param string $name
     *
     * @throws InvalidConfigurationException
     */
    public function getLinkProperty($name)
    {
        if (!in_array($name, array('update_link', 'delete_link'))) {
            throw new InvalidConfigurationException(sprintf('Invalid property type "%s"', $name));
        }

        $builder = new TreeBuilder();

        return $builder->root($name)
            ->requiresAtLeastOneElement()
            ->prototype('scalar')
                ->children()
                    ->scalarNode('type')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('route')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->arrayNode('params')
                    ->end()
                ->end()
            ->end();
    }
}
