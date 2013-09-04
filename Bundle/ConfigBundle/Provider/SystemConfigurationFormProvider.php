<?php

namespace Oro\Bundle\ConfigBundle\Provider;

use Oro\Bundle\ConfigBundle\Utils\TreeUtils;
use Symfony\Component\Form\FormFactoryInterface;

class SystemConfigurationFormProvider extends FormProvider
{
    const CORRECT_FIELDS_NESTING_LEVEL = 5;
    const CORRECT_TAB_NESTING_LEVEL    = 2;

    const TREE_NAME                    = 'system_configuration';

    /** @var FormFactoryInterface */
    protected $factory;

    public function __construct($config, FormFactoryInterface $factory)
    {
        parent::__construct($config);

        $this->factory = $factory;
    }

    /**
     * Builds form and prepares view blocks
     *
     * @param string $activeGroup
     * @return \Symfony\Component\Form\Form
     */
    public function getForm($activeGroup)
    {
        $block = $this->getSubtreeData(self::TREE_NAME, $activeGroup);

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

    public function chooseActiveGroups($activeGroup, $activeSubGroup)
    {
        $tree = $this->getTreeData(self::TREE_NAME);

        if ($activeGroup === null) {
            $firstGroup  = current($tree);
            if ($firstGroup !== false) {
                $activeGroup = $firstGroup['name'];
            }
        }

        // we can find active subgroup only in case if group is specified
        if ($activeSubGroup === null && $activeGroup !== null) {
            $subtree = TreeUtils::findNodeByName($tree, $activeGroup);

            $subGroups = TreeUtils::getByNestingLevel($subtree['children'], 1);
            if (!empty($subGroups)) {
                $firstGroup  = current($subGroups);
                if ($firstGroup !== false) {
                    $activeSubGroup = $firstGroup['name'];
                }
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
