<?php

namespace Oro\Bundle\FilterBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    const DEFAULT_LAYOUT = 'OroFilterBundle:Filter:layout.js.twig';
    const DEFAULT_HEADER = 'OroFilterBundle:Filter:header.html.twig';

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('oro_filter');

        $rootNode
            ->children()
                ->arrayNode('twig')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('layout')
                            ->cannotBeEmpty()
                            ->defaultValue(self::DEFAULT_LAYOUT)
                        ->end()
                        ->scalarNode('header')
                            ->cannotBeEmpty()
                            ->defaultValue(self::DEFAULT_HEADER)
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
