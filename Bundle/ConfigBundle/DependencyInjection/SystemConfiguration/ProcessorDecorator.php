<?php

namespace Oro\Bundle\ConfigBundle\DependencyInjection\SystemConfiguration;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

class ProcessorDecorator
{
    const ROOT                 = 'oro_system_configuration';
    const LEVELS_ROOT          = 'levels';
    const VERTICAL_TABS_ROOT   = 'vtabs';
    const HORIZONTAL_TABS_ROOT = 'htabs';
    const FIELDSETS_ROOT       = 'fieldsets';
    const FIELDS_ROOT          = 'fields';
    const TREE_ROOT            = 'tree';

    /** @var Processor */
    protected $processor;

    /**
     * @param array $data
     * @return array
     */
    public function process(array $data)
    {
        $result = $this->getProcessor()->process($this->getConfigurationTree()->buildTree(), $data);

        return $result;
    }

    /**
     * Getter for processor
     *
     * @return Processor
     */
    protected function getProcessor()
    {
        return $this->processor ? : new Processor();
    }

    /**
     * Getter for configuration tree
     *
     * @return TreeBuilder
     */
    protected function getConfigurationTree()
    {
        $tree = new TreeBuilder();

        $tree->root(self::ROOT)->children()
                ->append($this->getLevelsNode())
                ->append($this->getTabsNode(self::VERTICAL_TABS_ROOT))
                ->append($this->getTabsNode(self::HORIZONTAL_TABS_ROOT))
                ->append($this->getFieldsetsNode())
                ->append($this->getFieldsNode())
                ->append($this->getTreeNode())
            ->end();

        return $tree;
    }

    /**
     * @return NodeDefinition
     */
    protected function getLevelsNode()
    {
        $builder = new TreeBuilder();

        $node = $builder->root(self::LEVELS_ROOT)
            ->isRequired()
            ->requiresAtLeastOneElement()
            ->prototype('scalar')
        ->end();

        return $node;
    }

    /**
     * @param string $nodeName
     * @return NodeDefinition
     */
    protected function getTabsNode($nodeName)
    {
        $builder = new TreeBuilder();

        $node = $builder->root($nodeName)
            ->prototype('array')
                ->children()
                    ->scalarNode('label')->isRequired()->end()
                    ->scalarNode('icon')->end()
                ->end()
            ->end();

        return $node;
    }

    /**
     * @return NodeDefinition
     */
    protected function getFieldsetsNode()
    {
        $builder = new TreeBuilder();

        $node = $builder->root(self::FIELDSETS_ROOT)
            ->prototype('array')
                ->children()
                    ->scalarNode('label')->isRequired()->end()
                ->end()
            ->end();

        return $node;
    }

    /**
     * @return NodeDefinition
     */
    protected function getFieldsNode()
    {
        $builder = new TreeBuilder();

        $node = $builder->root(self::FIELDS_ROOT)
            ->prototype('array')
                ->children()
                    ->scalarNode('type')->isRequired()->end()
                    ->arrayNode('options')
                        ->prototype('variable')->end()
                    ->end()
                    ->arrayNode('levels')
                        ->isRequired()
                        ->requiresAtLeastOneElement()
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }

    /**
     * @return NodeDefinition
     */
    protected function getTreeNode()
    {
        $builder = new TreeBuilder();

        $node = $builder->root(self::TREE_ROOT)
            ->prototype('array')
                ->prototype('array')
                    ->prototype('array')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }
}
