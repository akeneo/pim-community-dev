<?php

namespace Oro\Bundle\AsseticBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
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
                ->arrayNode('js_debug')
                    ->prototype('scalar')->end()
                ->end()
                ->booleanNode('js_debug_all')->defaultValue(false)->end()
                ->arrayNode('css_debug')
                    ->prototype('scalar')->end()
                ->end()
                ->booleanNode('css_debug_all')->defaultValue(false)->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
