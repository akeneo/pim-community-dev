<?php

namespace Oro\Bundle\ConfigBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class SettingsBuilder
{
    /**
     *
     * @param ArrayNodeDefinition $root     Config root node
     * @param array               $settings
     */
    public static function append(ArrayNodeDefinition $root, $settings)
    {
        $builder = new TreeBuilder();
        $node = $builder
            ->root('settings')
            ->addDefaultsIfNotSet()
            ->children();

        foreach ($settings as $name => $setting) {
            $child = $node
                ->arrayNode($name)
                ->addDefaultsIfNotSet()
                ->children();

            $type = isset($setting['type']) && in_array($setting['type'], ['scalar', 'boolean', 'array'])
                ? $setting['type']
                : 'scalar';

            switch ($type) {
                case 'scalar':
                    $child->scalarNode('value')->defaultValue($setting['value']);

                    break;
                case 'boolean':
                case 'bool':
                    $child->booleanNode('value')->defaultValue((bool)$setting['value']);

                    break;
                case 'array':
                    $child->arrayNode('value');

                    break;
            }

            $child->scalarNode('scope')->defaultValue(isset($setting['scope']) ? $setting['scope'] : 'app');
        }

        $root->children()->append($node->end());
    }
}
