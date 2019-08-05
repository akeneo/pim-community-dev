<?php

namespace Akeneo\UserManagement\Bundle\DependencyInjection;

use Akeneo\UserManagement\Bundle\Form\Type\AclAccessLevelSelectorType;
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
            ->root('pim_user')
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
                            ->arrayNode('types')
                                ->prototype('scalar')
                                ->end()
                            ->end()
                            ->scalarNode('field_type')->end()
                            ->scalarNode('default_value')->end()
                            ->booleanNode('show_default')->end()
                        ->end()
                    ->end()
                    ->defaultValue(
                        [
                            'action'=> [
                                'label'         => 'pim_user.user.fields.capabilities',
                                'view_type'     => 'list',
                                'types'         => ['action'],
                                'field_type'    => AclAccessLevelSelectorType::class,
                                'default_value' => 1,
                                'show_default'  => false,
                            ]
                        ]
                    )
                ->end()
                ->arrayNode('login_form')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('action_route')
                            ->defaultValue('pim_user_security_check')
                            ->cannotBeEmpty()
                        ->end()
                        ->arrayNode('additional_hidden_fields')
                            ->defaultValue([])
                            ->requiresAtLeastOneElement()
                            ->useAttributeAsKey('name')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('field_name')->cannotBeEmpty()->end()
                                    ->scalarNode('initialized_by')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}
