<?php

namespace Oro\Bundle\ConfigBundle\Provider;

use Oro\Bundle\ConfigBundle\Utils\TreeUtils;
use Oro\Bundle\ConfigBundle\DependencyInjection\SystemConfiguration\ProcessorDecorator;

abstract class FormProvider implements ProviderInterface
{
    const TAG_NAME = 'oro_config.configuration_form_provider';

    /** @var array */
    protected $config;

    /** @var array */
    protected $processedTrees = array();

    /** @var array */
    protected $processedSubTrees = array();

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubtree($subtreeRootName)
    {
        if (!isset($this->processedSubTrees[$subtreeRootName])) {
            $treeData = $this->getTree();
            $subtree = TreeUtils::findNodeByName($treeData, $subtreeRootName);

            if ($subtree === null) {
                throw new \Exception(sprintf('Subtree "%s" not found', $subtreeRootName));
            }

            $this->processedSubTrees[$subtreeRootName] = $subtree;
        }

        return $this->processedSubTrees[$subtreeRootName];
    }

    /**
     * @param string $treeName
     * @param int $correctFieldsLevel
     * @throws \Exception
     * @return array
     */
    protected function getTreeData($treeName, $correctFieldsLevel)
    {
        if (!isset($this->processedTrees[$treeName])) {
            if (isset($this->config[ProcessorDecorator::TREE_ROOT][$treeName])) {
                $data = $this->buildGroupNode(
                    $this->config[ProcessorDecorator::TREE_ROOT][$treeName],
                    $correctFieldsLevel
                );
            } else {
                throw new \Exception(sprintf('Tree "%s" does not defined', $treeName));
            }

            $this->processedTrees[$treeName] = $data;
        }

        return $this->processedTrees[$treeName];
    }

    /**
     * Builds group node, called recursively
     *
     * @param array $nodes
     * @param int $correctFieldsLevel fields should be placed on the same levels that comes from view
     * @param int $level current level
     * @throws \Exception
     * @return mixed
     */
    protected function buildGroupNode($nodes, $correctFieldsLevel, $level = 0)
    {
        $level++;
        foreach ($nodes as $name => $node) {
            if (is_array($node) && isset($node['children'])) {
                $group = isset($this->config[ProcessorDecorator::GROUPS_NODE][$name])
                    ? $this->config[ProcessorDecorator::GROUPS_NODE][$name] : false;

                if ($group === false) {
                    throw new \Exception(sprintf('Group "%s" does not defined', $name));
                }

                $nodes[$name] = array_merge(array('name' => $name, 'priority' => 0), $group, $nodes[$name]);
                $nodes[$name]['children'] = $this->buildGroupNode($node['children'], $correctFieldsLevel, $level);
            } else {
                if ($level !== $correctFieldsLevel) {
                    throw new \Exception(
                        sprintf('Field "%s" will not be ever rendered. Please check nesting level', $node)
                    );
                }
                $nodes[$name] = $this->buildFieldNode($node);
            }
        }

        $nodes = TreeUtils::sortNodesByPriority($nodes);

        return $nodes;
    }

    /**
     * Builds field data by name
     *
     * @param string $node field node name
     * @return array
     * @throws \Exception
     */
    protected function buildFieldNode($node)
    {
        $field = isset($this->config[ProcessorDecorator::FIELDS_ROOT][$node])
            ? $this->config[ProcessorDecorator::FIELDS_ROOT][$node] : false;

        if ($field === false) {
            throw new \Exception(sprintf('Field "%s" does not defined', $node));
        }

        $field = array_merge(array('name' => $node, 'priority' => 0), $field);

        return $field;
    }

    /**
     * @param $name
     * @param $options
     * @return mixed
     */
    protected function newConstraint($name, $options)
    {
        if (strpos($name, '\\') !== false && class_exists($name)) {
            $className = (string) $name;
        } else {
            $className = 'Symfony\\Component\\Validator\\Constraints\\' . $name;
        }

        return new $className($options);
    }

    /**
     * @param array $nodes
     * @return array
     */
    protected function parseValidator(array $nodes)
    {
        $values = array();

        foreach ($nodes as $name => $childNodes) {
            if (is_numeric($name) && is_array($childNodes) && count($childNodes) == 1) {
                $options = current($childNodes);

                if (is_array($options)) {
                    $options = $this->parseValidator($options);
                }

                $values[] = $this->newConstraint(key($childNodes), $options);
            } else {
                if (is_array($childNodes)) {
                    $childNodes = $this->parseValidator($childNodes);
                }

                $values[$name] = $childNodes;
            }
        }

        return $values;
    }
}
