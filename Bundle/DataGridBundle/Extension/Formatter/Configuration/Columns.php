<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration;

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
                ->end()
                ->arrayNode('properties')
                ->end()
            ->end();

        return $builder;
    }
}
