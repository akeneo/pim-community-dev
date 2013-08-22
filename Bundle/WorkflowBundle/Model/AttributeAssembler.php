<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\WorkflowBundle\Exception\AssemblerException;
use Oro\Bundle\WorkflowBundle\Model\Attribute;

class AttributeAssembler extends AbstractAssembler
{
    /**
     * @param array $configuration
     * @return ArrayCollection
     * @throws AssemblerException If configuration is invalid
     */
    public function assemble(array $configuration)
    {
        $attributes = new ArrayCollection();
        foreach ($configuration as $name => $options) {
            $attribute = $this->assembleAttribute($name, $options);
            $attributes->set($name, $attribute);
        }

        return $attributes;
    }

    /**
     * @param string $name
     * @param array $options
     * @return Attribute
     */
    protected function assembleAttribute($name, array $options)
    {
        $options = $this->addDefaultOptions($options);

        $this->assertOptions($options, array('label', 'type'));

        $attribute = new Attribute();
        $attribute->setName($name);
        $attribute->setLabel($options['label']);
        $attribute->setType($options['type']);

        $attribute->setOptions($this->getOption($options, 'options', array()));

        $this->validateAttribute($attribute);

        return $attribute;
    }

    protected function addDefaultOptions(array $options)
    {
        if (isset($options['type']) && $options['type'] == 'entity') {
            $options['options'] = isset($options['options']) && is_array($options['options']) ?
                $options['options'] : array();

            $options['options'] = array_merge(
                array('multiple' => false, 'bind' => true),
                $options['options']
            );
        }
        return $options;
    }

    /**
     * @param Attribute $attribute
     * @throws AssemblerException If attribute is invalid
     */
    protected function validateAttribute(Attribute $attribute)
    {
        $this->assertAttributeHasValidType($attribute);
        
        if ($attribute->getType() == 'object' || $attribute->getType() == 'entity') {
            $this->assertAttributeHasClassOption($attribute);
        } else {
            $this->assertAttributeHasNoOptions($attribute, 'class');
        }

        if ($attribute->getType() == 'entity') {
            $multiple = $attribute->getOption('multiple');
            $bind = $attribute->getOption('bind');
            if (!$multiple && !$bind) {
                throw new AssemblerException(
                    sprintf(
                        'Options "multiple" and "bind" in attribute "%s" cannot be false simultaneously',
                        $attribute->getName()
                    )
                );
            }
        } else {
            $this->assertAttributeHasNoOptions($attribute, array('managed_entity', 'bind', 'multiple'));
        }
    }

    /**
     * @param Attribute $attribute
     * @throws AssemblerException If attribute is invalid
     */
    protected function assertAttributeHasValidType(Attribute $attribute)
    {
        $attributeType = $attribute->getType();
        $allowedTypes = array('bool', 'boolean', 'int', 'integer', 'float', 'string', 'array', 'object', 'entity');

        if (!in_array($attributeType, $allowedTypes)) {
            throw new AssemblerException(
                sprintf(
                    'Invalid attribute type "%s", allowed types are "%s"',
                    $attributeType,
                    implode('", "', $allowedTypes)
                )
            );
        }
    }

    /**
     * @param Attribute $attribute
     * @param string|array $optionNames
     * @throws AssemblerException If attribute is invalid
     */
    protected function assertAttributeHasOptions(Attribute $attribute, $optionNames)
    {
        $optionNames = (array)$optionNames;

        foreach ($optionNames as $optionName) {
            if (!$attribute->hasOption($optionName)) {
                throw new AssemblerException(
                    sprintf('Option "%s" is required in attribute "%s"', $optionName, $attribute->getName())
                );
            }
        }
    }

    /**
     * @param Attribute $attribute
     * @param string|array $optionNames
     * @throws AssemblerException If attribute is invalid
     */
    protected function assertAttributeHasNoOptions(Attribute $attribute, $optionNames)
    {
        $optionNames = (array)$optionNames;

        foreach ($optionNames as $optionName) {
            if ($attribute->hasOption($optionName)) {
                throw new AssemblerException(
                    sprintf('Option "%s" cannot be used in attribute "%s"', $optionName, $attribute->getName())
                );
            }
        }
    }

    /**
     * @param Attribute $attribute
     * @throws AssemblerException If attribute is invalid
     */
    protected function assertAttributeHasClassOption(Attribute $attribute)
    {
        $this->assertAttributeHasOptions($attribute, 'class');
        if (!class_exists($attribute->getOption('class'))) {
            throw new AssemblerException(
                sprintf(
                    'Class "%s" referenced by "class" option in attribute "%s" not found',
                    $attribute->getOption('class'),
                    $attribute->getName()
                )
            );
        }
    }
}
