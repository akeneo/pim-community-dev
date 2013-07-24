<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\WorkflowBundle\Model\Attribute;

class AttributeAssembler
{
    /**
     * @param array $configuration
     * @return ArrayCollection
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
        $attributeOptions = !empty($options['options']) ? $options['options'] : array();

        $attribute = new Attribute();
        $attribute->setName($name);
        $attribute->setLabel($options['label']);
        $attribute->setFormTypeName($options['form_type']);
        $attribute->setOptions($attributeOptions);

        return $attribute;
    }
}
