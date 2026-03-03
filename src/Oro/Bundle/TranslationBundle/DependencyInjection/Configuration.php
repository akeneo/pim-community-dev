<?php

namespace Oro\Bundle\TranslationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('oro_translation');
        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('js_translation')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('domains')
                            ->requiresAtLeastOneElement()
                            ->defaultValue(['jsmessages', 'validators'])
                            ->prototype('scalar')
                            ->end()
                        ->end()
                        ->booleanNode('debug')
                            ->defaultValue('%kernel.debug%')
                        ->end()
                    ->end()
                ->end()
            ->end();
        return $treeBuilder;
    }
}
