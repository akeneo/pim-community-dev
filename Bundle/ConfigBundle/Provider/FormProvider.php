<?php

namespace Oro\Bundle\ConfigBundle\Provider;

use Symfony\Component\Form\FormBuilderInterface;

use Oro\Bundle\ConfigBundle\Utils\TreeUtils;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\Config\Tree\FieldNodeDefinition;
use Oro\Bundle\ConfigBundle\Config\Tree\GroupNodeDefinition;
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
            $subtree  = TreeUtils::findNodeByName($treeData, $subtreeRootName);

            if ($subtree === null) {
                throw new \Exception(sprintf('Subtree "%s" not found', $subtreeRootName));
            }

            $this->processedSubTrees[$subtreeRootName] = $subtree;
        }

        return $this->processedSubTrees[$subtreeRootName];
    }

    /**
     * @param string $treeName
     * @param int    $correctFieldsLevel
     *
     * @throws \Exception
     * @return GroupNodeDefinition
     */
    protected function getTreeData($treeName, $correctFieldsLevel)
    {
        if (!isset($this->processedTrees[$treeName])) {
            if (!isset($this->config[ProcessorDecorator::TREE_ROOT][$treeName])) {
                throw new \Exception(sprintf('Tree "%s" does not defined', $treeName));
            }

            $definition                             = $this->config[ProcessorDecorator::TREE_ROOT][$treeName];
            $data                                   = $this->buildGroupNode($definition, $correctFieldsLevel);
            $tree                                   = new GroupNodeDefinition($treeName, $definition, $data);
            $this->processedTrees[$tree->getName()] = $tree;
        }

        return $this->processedTrees[$treeName];
    }

    /**
     * Builds group node, called recursively
     *
     * @param array $nodes
     * @param int   $correctFieldsLevel fields should be placed on the same levels that comes from view
     * @param int   $level              current level
     *
     * @throws \Exception
     * @return array
     */
    protected function buildGroupNode($nodes, $correctFieldsLevel, $level = 0)
    {
        $level++;
        foreach ($nodes as $name => $node) {
            if (is_array($node) && isset($node['children'])) {
                if (!isset($this->config[ProcessorDecorator::GROUPS_NODE][$name])) {
                    throw new \Exception(sprintf('Group "%s" does not defined', $name));
                }

                $group = $this->config[ProcessorDecorator::GROUPS_NODE][$name];
                $data  = $this->buildGroupNode($node['children'], $correctFieldsLevel, $level);
                $node  = new GroupNodeDefinition($name, array_merge($group, $nodes[$name]), $data);
                $node->setLevel($level);

                $nodes[$node->getName()] = $node;
            } else {
                if ($level !== $correctFieldsLevel) {
                    throw new \Exception(
                        sprintf('Field "%s" will not be ever rendered. Please check nesting level', $node)
                    );
                }
                $nodes[$name] = $this->buildFieldNode($node);
            }
        }

        return $nodes;
    }

    /**
     * Builds field data by name
     *
     * @param string $node field node name
     *
     * @return FieldNodeDefinition
     * @throws \Exception
     */
    protected function buildFieldNode($node)
    {
        if (!isset($this->config[ProcessorDecorator::FIELDS_ROOT][$node])) {
            throw new \Exception(sprintf('Field "%s" does not defined', $node));
        }

        return new FieldNodeDefinition($node, $this->config[ProcessorDecorator::FIELDS_ROOT][$node]);
    }


    protected function addFieldToForm(FormBuilderInterface $form, FieldNodeDefinition $fieldDefinition)
    {
        if ($fieldDefinition->getAclResource() && !$this->checkIsGranted($fieldDefinition->getAclResource())) {
            // field is not allowed to be shown, do nothing
            return;
        }

        $name = str_replace(
            ConfigManager::SECTION_MODEL_SEPARATOR,
            ConfigManager::SECTION_VIEW_SEPARATOR,
            $fieldDefinition->getName()
        );

        $form->add($name, 'oro_config_form_field_type', $fieldDefinition->toFormFieldOptions());
    }

    /**
     * Check ACL resource
     *
     * @param string $resourceName
     *
     * @return mixed
     */
    abstract protected function checkIsGranted($resourceName);
}
