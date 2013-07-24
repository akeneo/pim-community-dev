<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\WorkflowBundle\Model\Step;
use Oro\Bundle\WorkflowBundle\Model\Attribute;
use Oro\Bundle\WorkflowBundle\Exception\UnknownAttributeException;

class StepAssembler extends AbstractAssembler
{
    /**
     * @param array $configuration
     * @param Attribute[]|ArrayCollection $attributes
     * @return ArrayCollection
     */
    public function assemble(array $configuration, $attributes)
    {
        $steps = new ArrayCollection();
        foreach ($configuration as $name => $options) {
            $step = $this->assembleStep($name, $options, $attributes);
            $steps->set($name, $step);
        }

        return $steps;
    }

    /**
     * @param string $name
     * @param array $options
     * @param Attribute[]|ArrayCollection $attributes
     * @return Step
     */
    protected function assembleStep($name, array $options, $attributes)
    {
        $allowedTransitions = !empty($options['allowed_transitions']) ? $options['allowed_transitions'] : array();
        $stepAttributes = !empty($options['attributes'])
            ? $this->assembleStepAttributes($options['attributes'], $attributes)
            : array();

        $step = new Step();
        $step->setName($name);
        $step->setLabel($options['label']);
        $step->setTemplate($options['template']);
        $step->setOrder($options['order']);
        $step->setIsFinal($options['is_final']);
        $step->setAllowedTransitions($allowedTransitions);
        $step->setAttributes($stepAttributes);

        return $step;
    }

    /**
     * @param array $stepAttributeNames
     * @param Attribute[]|ArrayCollection $attributes
     * @return ArrayCollection
     * @throws UnknownAttributeException
     */
    protected function assembleStepAttributes(array $stepAttributeNames, $attributes)
    {
        $stepAttributes = new ArrayCollection();
        foreach ($stepAttributeNames as $stepAttributeName) {
            if (!isset($attributes[$stepAttributeName])) {
                throw new UnknownAttributeException(sprintf('Unknown attribute %s', $stepAttributeName));
            }

            $stepAttributes->set($stepAttributeName, $attributes[$stepAttributeName]);
        }

        return $stepAttributes;
    }
}
