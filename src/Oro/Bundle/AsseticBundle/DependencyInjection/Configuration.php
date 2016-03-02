<?php

namespace Oro\Bundle\AsseticBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('oro_assetic');

        $rootNode
            ->children()
                ->arrayNode('css_debug')
                    ->prototype('scalar')->end()
                ->end()
                ->booleanNode('css_debug_all')->defaultValue(false)->end()
            ->end();

        return $treeBuilder;
    }
}
