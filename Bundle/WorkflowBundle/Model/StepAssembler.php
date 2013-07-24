<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Oro\Bundle\WorkflowBundle\Model\Step;
use Oro\Bundle\WorkflowBundle\Model\StepAttribute;
use Doctrine\Common\Collections\ArrayCollection;

class StepAssembler
{
    /**
     * @param array $configuration
     * @return ArrayCollection
     */
    public function assemble(array $configuration)
    {
        $steps = new ArrayCollection();
        foreach ($configuration as $stepName => $stepOptions) {
            $step = $this->assembleStep($stepName, $stepOptions);
            $steps->set($stepName, $step);
        }

        return $steps;
    }

    /**
     * @param string $name
     * @param array $options
     * @return Step
     */
    protected function assembleStep($name, array $options)
    {
        $allowedTransitions = !empty($options['allowed_transitions']) ? $options['allowed_transitions'] : array();
        $attributes = !empty($options['attributes']) ? $this->assembleStepAttributes($options['attributes']) : array();

        $step = new Step();
        $step->setName($name);
        $step->setLabel($options['label']);
        $step->setTemplate($options['template']);
        $step->setOrder($options['order']);
        $step->setIsFinal($options['is_final']);
        $step->setAllowedTransitions($allowedTransitions);
        $step->setAttributes($attributes);

        return $step;
    }

    /**
     * @param array $configuration
     * @return StepAttribute[]
     */
    protected function assembleStepAttributes(array $configuration)
    {
        $attributes = array();
        foreach ($configuration as $attributeName => $attributeOptions) {
            $attributes[$attributeName] = $this->assembleStepAttribute($attributeName, $attributeOptions);
        }

        return $attributes;
    }

    /**
     * @param string $name
     * @param array $options
     * @return StepAttribute
     */
    protected function assembleStepAttribute($name, array $options)
    {
        $attributeOptions = !empty($options['options']) ? $options['options'] : array();

        $stepAttribute = new StepAttribute();
        $stepAttribute->setName($name);
        $stepAttribute->setLabel($options['label']);
        $stepAttribute->setFormTypeName($options['form_type']);
        $stepAttribute->setOptions($attributeOptions);

        return $stepAttribute;
    }
}
