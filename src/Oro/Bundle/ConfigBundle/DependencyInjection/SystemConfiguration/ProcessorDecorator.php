<?php

namespace Oro\Bundle\ConfigBundle\DependencyInjection\SystemConfiguration;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

class ProcessorDecorator
{
    const ROOT = 'oro_system_configuration';
    const GROUPS_NODE = 'groups';
    const FIELDS_ROOT = 'fields';
    const TREE_ROOT = 'tree';

    /** @var Processor */
    protected $processor;

    /**
     * @param array $data
     *
     * @return array
     */
    public function process(array $data)
    {
        $result = $this->getProcessor()->process($this->getConfigurationTree()->buildTree(), $data);

        return $result;
    }

    /**
     * Merge configs by specified rules
     *
     * @param array $source
     * @param array $newData
     *
     * @return array
     */
    public function merge($source, $newData)
    {
        // prevent key isset and is_array checks
        $source = array_merge($this->getEmptyFinalArray(), $source);

        if (!empty($newData[self::ROOT])) {
            foreach ((array)$newData[self::ROOT] as $nodeName => $node) {
                switch ($nodeName) {
                    // merge recursive all nodes in tree
                    case self::TREE_ROOT:
                        $source[self::ROOT][$nodeName] = array_merge_recursive(
                            $source[self::ROOT][$nodeName],
                            $node
                        );
                        break;
                    // replace all overrides in other nodes
                    default:
                        $source[self::ROOT][$nodeName] = array_replace_recursive(
                            $source[self::ROOT][$nodeName],
                            $node
                        );
                }
            }
        }

        return $source;
    }

    /**
     * Returns empty array representation of valid config structure
     *
     * @return array
     */
    protected function getEmptyFinalArray()
    {
        $result = [
            self::ROOT => array_fill_keys(
                [self::GROUPS_NODE, self::FIELDS_ROOT, self::TREE_ROOT],
                []
            )
        ];

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

        $tree->root(self::ROOT)
            ->children()
                ->append($this->getGroupsNode())
                ->append($this->getFieldsNode())
                ->append($this->getTreeNode())
            ->end();

        return $tree;
    }

    /**
     * @return NodeDefinition
     */
    protected function getGroupsNode()
    {
        $builder = new TreeBuilder();

        $node = $builder->root(self::GROUPS_NODE)
            ->prototype('array')
                ->children()
                    ->scalarNode('title')->isRequired()->end()
                    ->scalarNode('icon')->end()
                    ->scalarNode('description')->end()
                    ->integerNode('priority')->end()
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
                    ->scalarNode('acl_resource')->end()
                    ->integerNode('priority')->end()
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
                    ->children()
                        ->arrayNode('children')
                            ->prototype('array')
                                ->prototype('variable')->end()
                            ->end()
                        ->end()
                        ->integerNode('priority')->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }
}
