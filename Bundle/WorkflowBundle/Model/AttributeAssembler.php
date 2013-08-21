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
        $managedEntityAttributes = 0;
        foreach ($configuration as $name => $options) {
            $attribute = $this->assembleAttribute($name, $options);
            if ($attribute->getOption('managed_entity')) {
                $managedEntityAttributes++;
            }
            $attributes->set($name, $attribute);
        }

        if ($managedEntityAttributes > 1) {
            throw new AssemblerException('More than one attribute with "managed_entity" option is not allowed');
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
        $this->assertOptions($options, array('label', 'type'));

        $attribute = new Attribute();
        $attribute->setName($name);
        $attribute->setLabel($options['label']);
        $attribute->setType($options['type']);
        $attribute->setOptions($this->getOption($options, 'options', array()));

        $this->validateAttribute($attribute);

        return $attribute;
    }

    /**
     * @param Attribute $attribute
     * @throws AssemblerException If attribute is invalid
     */
    protected function validateAttribute(Attribute $attribute)
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

        $classRequired = ($attributeType == 'object' || $attributeType == 'entity');
        if ($classRequired && !$attribute->hasOption('class')) {
            throw new AssemblerException(
                sprintf('Option "class" is required for attribute with type "%s"', $attributeType)
            );
        } elseif (!$classRequired && $attribute->hasOption('class')) {
            throw new AssemblerException(
                sprintf('Option "class" cannot be used in attribute with type "%s"', $attributeType)
            );
        }

        if ($attribute->hasOption('class') && !class_exists($attribute->getOption('class'))) {
            throw new AssemblerException(
                sprintf('Class "%s" referenced by "class" option not found', $attribute->getOption('class'))
            );
        }

        if ($attribute->hasOption('managed_entity') && $attribute->getType() !== 'entity') {
            throw new AssemblerException(
                sprintf('Option "managed_entity" cannot be used with attribute type "%s"', $attributeType)
            );
        }
    }
}
