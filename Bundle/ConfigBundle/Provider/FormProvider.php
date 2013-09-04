<?php

namespace Oro\Bundle\ConfigBundle\Provider;

use Oro\Bundle\ConfigBundle\DependencyInjection\SystemConfiguration\ProcessorDecorator;
use Oro\Bundle\ConfigBundle\Utils\TreeUtils;

abstract class FormProvider implements ProviderInterface
{
    const TAG_NAME = 'oro_config.configuration_form_provider';

    /** @var array */
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * @param string $treeName
     * @param string $subtreeRootName
     * @return array|null
     * @throws \Exception
     */
    public function getSubtreeData($treeName, $subtreeRootName)
    {
        $treeData = $this->getTreeData($treeName);

        $subtree = TreeUtils::findNodeByName($treeData, $subtreeRootName);

        if ($subtree === null) {
            throw new \Exception(sprintf('Subtree "%s" not found', $subtreeRootName));
        }

        return $subtree;
    }

    /**
     * @param string $treeName
     * @param int $correctFieldsLevel Default value is correct level for system configuration tree
     * @throws \Exception
     * @return array
     */
    public function getTreeData($treeName, $correctFieldsLevel = 5)
    {
        if (isset($this->config[ProcessorDecorator::TREE_ROOT][$treeName])) {
            $data = $this->buildGroupNode(
                $this->config[ProcessorDecorator::TREE_ROOT][$treeName],
                $correctFieldsLevel
            );
        } else {
            throw new \Exception(sprintf('Tree "%s" does not defined', $treeName));
        }

        return $data;
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

                $nodes[$name] = array_merge($group, $nodes[$name], array('name' => $name, 'priority' => 0));
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

        TreeUtils::sortNodesByPriority($nodes);

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

        $field = array_merge($field, array('name' => $node, 'priority' => 0));

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
