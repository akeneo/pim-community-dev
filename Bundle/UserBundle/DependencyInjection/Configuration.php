<?php

namespace Oro\Bundle\UserBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();
        $builder
            ->root('oro_user')
            ->children()
                ->arrayNode('reset')
                    ->addDefaultsIfNotSet()
                    ->canBeUnset()
                    ->children()
                        ->scalarNode('ttl')
                            ->defaultValue(86400)
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('email')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('address')
                            ->defaultValue('no-reply@example.com')
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('name')
                            ->defaultValue('Oro Admin')
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('privileges')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('label')->end()
                            ->scalarNode('view_type')->end()
                            ->arrayNode('types')->end()
                            ->scalarNode('field_type')->end()
                            ->booleanNode('fix_values')->end()
                            ->scalarNode('default_value')->end()
                            ->booleanNode('show_default')->end()
                        ->end()
                    ->end()
                    ->defaultValue(
                        array(
                            'entity'=>array(
                                'label' => 'Entity',
                                'view_type' => 'grid',
                                'types' => array('entity'),
                                'field_type' => 'oro_acl_access_level_selector',
                                'fix_values' => false,
                                'default_value' => 5,
                                'show_default' => true,
                            ),
                            'action'=>array(
                                'label' => 'Capabilities',
                                'view_type' => 'list',
                                'types' => array('action'),
                                'field_type' => 'oro_acl_access_level_selector',
                                'fix_values' => false,
                                'default_value' => 1,
                                'show_default' => false,
                            )
                        )
                    )
                ->end()
            ->end();

        return $builder;
    }
}
