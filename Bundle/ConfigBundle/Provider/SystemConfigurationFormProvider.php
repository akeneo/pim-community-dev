<?php

namespace Oro\Bundle\ConfigBundle\Provider;

use Oro\Bundle\ConfigBundle\Utils\TreeUtils;
use Symfony\Component\Form\FormFactoryInterface;

class SystemConfigurationFormProvider extends FormProvider
{
    const TREE_NAME                    = 'system_configuration';
    const CORRECT_FIELDS_NESTING_LEVEL = 5;

    /** @var FormFactoryInterface */
    protected $factory;

    public function __construct($config, FormFactoryInterface $factory)
    {
        parent::__construct($config);

        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function getForm($activeGroup)
    {
        $block = $this->getSubtree($activeGroup);

        $toAdd = array();
        $blockConfig = array($activeGroup => $this->retrieveConfigFromDefinition($block));

        if (!empty($block['children'])) {
            $subBlocksConfig = array();

            foreach ($block['children'] as $subblock) {
                $subBlockName = $subblock['name'];
                $subBlocksConfig[$subBlockName] = $this->retrieveConfigFromDefinition($subblock);

                if (!empty($subblock['children'])) {
                    foreach ($subblock['children'] as $field) {
                        $options = !empty($field['options']) ? $field['options'] : array();
                        $field['options'] = array_merge(
                            $options,
                            array(
                                'block'    => $block['name'],
                                'subblock' => $subblock['name']
                            )
                        );

                        if (isset($field['options']['constraints'])) {
                            $field['options']['constraints'] = $this->parseValidator($field['options']['constraints']);
                        }

                        $toAdd[] = $field;
                    }
                }
            }

            $blockConfig[$activeGroup]['subblocks'] = $subBlocksConfig;
        }

        $builder = $this->factory->createNamedBuilder(
            $activeGroup,
            'oro_config_form_type',
            null,
            array(
                'block_config' => $blockConfig
            )
        );
        foreach ($toAdd as $field) {
            $builder->add($field['name'], $field['type'], $field['options']);
        }

        return $builder->getForm();
    }

    /**
     * {@inheritdoc}
     */
    public function getTree()
    {
        return $this->getTreeData(self::TREE_NAME, self::CORRECT_FIELDS_NESTING_LEVEL);
    }

    /**
     * Lookup for first available groups if they are not specified yet
     *
     * @param string $activeGroup
     * @param string $activeSubGroup
     * @return array
     */
    public function chooseActiveGroups($activeGroup, $activeSubGroup)
    {
        $tree = $this->getTree();

        if ($activeGroup === null) {
            $activeGroup = TreeUtils::getFirstNodeName($tree);
        }

        // we can find active subgroup only in case if group is specified
        if ($activeSubGroup === null && $activeGroup) {
            $subtree = TreeUtils::findNodeByName($tree, $activeGroup);

            if (!empty($subtree)) {
                $subGroups      = TreeUtils::getByNestingLevel($subtree['children'], 1);
                $activeSubGroup = TreeUtils::getFirstNodeName($subGroups);
            }
        }

        return array($activeGroup, $activeSubGroup);
    }

    /**
     * Retrieve block config from group node definition
     *
     * @param array $definition
     * @return array
     */
    protected function retrieveConfigFromDefinition(array $definition)
    {
        return array_intersect_key($definition, array_flip(array('title', 'priority', 'description')));
    }
}
