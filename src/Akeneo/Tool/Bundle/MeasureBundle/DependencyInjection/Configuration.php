<?php

namespace Akeneo\Tool\Bundle\MeasureBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Measure configuration
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('akeneo_measure');

        $rootNode->children()
            ->arrayNode('measures_config')
            ->prototype('array')
            ->children()

                // standard unit (used as reference for conversion)
                ->scalarNode('standard')
                ->isRequired()
                ->end()

                // units of this group
                ->arrayNode('units')
                ->prototype('array')
                ->children()

                    ->append($this->addConvertNode())

                    ->scalarNode('symbol')
                    ->isRequired()
                    ->end()
                ->end()

            ->end();

        return $treeBuilder;
    }

    /**
     * Create a node definition for operations (could be extended to define new operations)
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition|
     *         \Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    protected function addConvertNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('convert');

        $node->requiresAtLeastOneElement()
            ->prototype('array')
                ->children()

                    ->scalarNode('add')
                    ->cannotBeEmpty()
                    ->end()

                    ->scalarNode('sub')
                    ->cannotBeEmpty()
                    ->end()

                    ->scalarNode('mul')
                    ->cannotBeEmpty()
                    ->end()

                    ->scalarNode('div')
                    ->cannotBeEmpty()
                    ->end()

                ->end()
            ->end();

        return $node;
    }
}
