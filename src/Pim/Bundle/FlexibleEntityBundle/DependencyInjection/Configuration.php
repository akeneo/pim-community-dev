<?php

namespace Pim\Bundle\FlexibleEntityBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Flexible entity configuration
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('pim_flexibleentity');

        $rootNode->children()

            ->append($this->addEntityNode())

        ->end();

        return $treeBuilder;
    }

    /**
     * Return flexible entity configuration
     * @return \Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    public function addEntityNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('entities_config');

        $node
            ->prototype('array')
            ->children()

                // required to setup a minimal flexible entity
                ->scalarNode('flexible_manager')
                    ->isRequired()
                ->end()

                ->scalarNode('flexible_class')
                    ->isRequired()
                ->end()

                ->scalarNode('flexible_value_class')
                    ->isRequired()
                ->end()

                // optional, to define customized attribute and option models
                ->scalarNode('attribute_class')
                    ->defaultValue('Pim\Bundle\FlexibleEntityBundle\Entity\Attribute')
                ->end()

                ->scalarNode('attribute_option_class')
                    ->defaultValue('Pim\Bundle\FlexibleEntityBundle\Entity\AttributeOption')
                ->end()

                ->scalarNode('attribute_option_value_class')
                    ->defaultValue('Pim\Bundle\FlexibleEntityBundle\Entity\AttributeOptionValue')
                ->end()

                // optional, to define customized media entity
                ->scalarNode('attribute_media_class')
                    ->defaultValue('Pim\Bundle\FlexibleEntityBundle\Entity\Media')
                ->end()

                // optional, default locale used for entity values
                ->scalarNode('default_locale')
                    ->defaultValue('en')
                ->end()

                // optional, default scope used for entity values
                ->scalarNode('default_scope')
                    ->defaultValue(null)
                ->end()

                // optional, init mode for flexible, add a value for each attribute, for required, or don't add values
                ->scalarNode('flexible_init_mode')
                    ->defaultValue('empty')
                    ->validate()
                    ->ifNotInArray(array('all_attributes', 'required_attributes', 'empty'))
                        ->thenInvalid('Invalid flexible init mode "%s"')
                    ->end()
                ->end()

            ->end()
        ->end();

        return $node;
    }
}
