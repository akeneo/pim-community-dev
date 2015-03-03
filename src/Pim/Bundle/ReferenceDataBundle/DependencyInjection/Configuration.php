<?php

namespace Pim\Bundle\ReferenceDataBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('pim_reference_data');

        $rootNode
            ->prototype('array')
                ->children()
                    ->scalarNode('class')->isRequired()->canNotBeEmpty()->end()
                    ->enumNode('type')->isRequired()->values(['simple', 'multi'])->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
