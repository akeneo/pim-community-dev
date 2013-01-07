<?php

namespace Oro\Bundle\FlexibleEntityBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('oro_flexibleentity');

        $rootNode->children()
            ->arrayNode('entities_config')
            ->prototype('array')
                ->children()

                    // required to setup a minimal flexible entity
                    ->scalarNode('flexible_manager')
                    ->isRequired()
                    ->end()

                    ->scalarNode('flexible_entity_class')
                    ->isRequired()
                    ->end()

                    ->scalarNode('flexible_entity_value_class')
                    ->isRequired()
                    ->end()

                    // optional, to define to customize attribute and option models
                    ->scalarNode('flexible_attribute_class')
                    ->defaultValue('Oro\Bundle\FlexibleEntityBundle\Entity\OrmEntityAttribute')
                    ->end()

                    ->scalarNode('flexible_attribute_option_class')
                    ->defaultValue('Oro\Bundle\FlexibleEntityBundle\Entity\OrmEntityAttributeOption')
                    ->end()

                    ->scalarNode('flexible_attribute_option_value_class')
                    ->defaultValue('Oro\Bundle\FlexibleEntityBundle\Entity\OrmEntityAttributeOptionValue')
                    ->end()

                    // optional behaviors
                    ->booleanNode('has_translatable_value')
                    ->defaultFalse()
                    ->end()

                    ->booleanNode('has_scopable_value')
                    ->defaultFalse()
                    ->end()

                ->end()
            ->end()


        ;

        return $treeBuilder;
    }
}
