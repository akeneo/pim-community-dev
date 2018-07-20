<?php

namespace Akeneo\Pim\Enrichment\Bundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('akeneo_pim_enrichment');

        $rootNode
            ->children()
                ->arrayNode('localization')
                    ->isRequired()
                    ->children()
                        ->arrayNode('decimal_separators')
                            ->isRequired()
                            ->requiresAtLeastOneElement()
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('value')->isRequired()->canNotBeEmpty()->end()
                                    ->scalarNode('label')->isRequired()->canNotBeEmpty()->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('date_formats')
                            ->isRequired()
                            ->requiresAtLeastOneElement()
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('value')->isRequired()->canNotBeEmpty()->end()
                                    ->scalarNode('label')->isRequired()->canNotBeEmpty()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
