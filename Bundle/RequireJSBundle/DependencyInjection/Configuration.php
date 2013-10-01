<?php

namespace Oro\Bundle\RequireJSBundle\DependencyInjection;

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
        $treeBuilder->root('oro_require_js')
            ->children()
                ->scalarNode('config_path')
                    ->defaultValue('js/require-config.js')
                ->end()
                ->arrayNode('config')
                    ->addDefaultsIfNotSet()
                    ->children()
                        // @see http://requirejs.org/docs/api.html#config-waitSeconds
                        ->integerNode('waitSeconds')
                            ->min(0)
                        ->end()
                        // @see http://requirejs.org/docs/api.html#config-enforceDefine
                        ->booleanNode('enforceDefine')->end()
                        // @see http://requirejs.org/docs/api.html#config-scriptType
                        ->scalarNode('scriptType')
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
