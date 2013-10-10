<?php

namespace Oro\Bundle\HelpBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('oro_help');

        $rootNode
            ->children()
                ->scalarNode('server')
                    ->cannotBeEmpty()
                    ->defaultValue('http://wiki.orocrm.com')
                ->end()
                ->scalarNode('prefix')
                    ->cannotBeEmpty()
                    ->defaultValue('Third_Party')
                ->end();

        return $treeBuilder;
    }
}
