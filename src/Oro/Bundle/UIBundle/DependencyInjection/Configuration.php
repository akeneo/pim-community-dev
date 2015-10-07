<?php

namespace Oro\Bundle\UIBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see
 * {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('oro_ui');

        $rootNode->children()
            ->scalarNode('wrap_class')
                ->cannotBeEmpty()
                ->defaultValue('block-wrap')
                ->end()
            ->arrayNode('placeholders_items')
                ->useAttributeAsKey('name')
                ->prototype('array')
                ->children()
                    ->arrayNode('items')
                    ->prototype('array')
                        ->children()
                            ->booleanNode('remove')->defaultValue(false)->end()
                            ->scalarNode('placeholder')->end()
                            ->scalarNode('order')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->end();

        SettingsBuilder::append(
            $rootNode,
            array(
                'application_name' => array(
                    'value' => 'ORO',
                    'type' => 'scalar'
                ),
                'application_title' => array(
                    'value' => 'ORO Business Application Platform',
                    'type' => 'scalar'
                ),
            )
        );

        return $treeBuilder;
    }
}
