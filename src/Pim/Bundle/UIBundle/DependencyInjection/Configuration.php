<?php

namespace Pim\Bundle\UIBundle\DependencyInjection;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class configuration
 *
 * @author    Marie Minasyan <marie.minasyan@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder
            ->root('pim_ui')
                ->children()
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
                    ->booleanNode('loading_message_enabled')
                        ->defaultTrue()
                    ->end()
                ->end()
            ->end();

        SettingsBuilder::append(
            $rootNode,
            [
                'application_name' => [
                    'value' => 'Akeneo',
                    'type'  => 'scalar'
                ],
                'application_title' => [
                    'value' => 'Akeneo',
                    'type'  => 'scalar'
                ],
                'loading_message_enabled' => ['value' => true]
            ]
        );

        return $treeBuilder;
    }
}
