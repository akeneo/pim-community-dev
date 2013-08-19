<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\WorkflowBundle\Model\Attribute;

class AttributeAssembler extends AbstractAssembler
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
        $this->assertOptions($options, array('label', 'type'));

        $attribute = new Attribute();
        $attribute->setName($name);
        $attribute->setLabel($options['label']);
        $attribute->setType($options['type']);
        $attribute->setOptions($this->getOption($options, 'options', array()));

        return $attribute;
    }
}
