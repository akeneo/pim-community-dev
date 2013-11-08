<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\WorkflowBundle\Exception\UnknownAttributeException;
use Oro\Bundle\WorkflowBundle\Form\Type\WorkflowStepType;
use Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException;

class StepAssembler extends AbstractAssembler
{
    /**
     * @param array $configuration
     * @param Attribute[]|Collection $attributes
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
     * @param Attribute[]|Collection $attributes
     * @return Step
     * @throws InvalidParameterException
     * @throws UnknownAttributeException
     */
    protected function assembleStep($name, array $options, $attributes)
    {
        $this->assertOptions($options, array('label'));

        $formOptions = $this->getOption($options, 'form_options', array());

        // each attribute field must be correspond to existing attribute
        $existingAttributeNames = $this->getAttributeNames($attributes);
        $attributeFields = $this->getOption($formOptions, 'attribute_fields', array());
        if (!is_array($attributeFields)) {
            throw new InvalidParameterException(
                sprintf('Option "attribute_fields" at step "%s" must be an array', $name)
            );
        }

        foreach (array_keys($attributeFields) as $attributeName) {
            if (!in_array($attributeName, $existingAttributeNames)) {
                throw new UnknownAttributeException(
                    sprintf('Unknown attribute "%s" at step "%s"', $attributeName, $name)
                );
            }
        }

        $viewAttributes = $this->assembleViewAttributes(
            $this->getOption($formOptions, 'view_attributes', array()),
            $name,
            $existingAttributeNames
        );

        $step = new Step();
        $step->setName($name)
            ->setLabel($options['label'])
            ->setTemplate($this->getOption($options, 'template', null))
            ->setOrder($this->getOption($options, 'order', 0))
            ->setIsFinal($this->getOption($options, 'is_final', false))
            ->setAllowedTransitions($this->getOption($options, 'allowed_transitions', array()))
            ->setFormType($this->getOption($options, 'form_type', WorkflowStepType::NAME))
            ->setFormOptions($formOptions)
            ->setViewAttributes($viewAttributes);

        return $step;
    }

    /**
     * @param array $viewAttributesOptions
     * @param string $stepName
     * @param array $existingAttributeNames
     * @return array
     * @throws UnknownAttributeException
     */
    protected function assembleViewAttributes(array $viewAttributesOptions, $stepName, array $existingAttributeNames)
    {
        $result = array();
        foreach ($viewAttributesOptions as $viewAttributeOptions) {
            if (isset($viewAttributeOptions['attribute'])) {
                $attributeName = $viewAttributeOptions['attribute'];
                if (!in_array($viewAttributeOptions['attribute'], $existingAttributeNames)) {
                    throw new UnknownAttributeException(
                        sprintf('Unknown attribute "%s" at step "%s"', $attributeName, $stepName)
                    );
                }
            }
            $result[] = new StepViewAttribute($viewAttributeOptions);
        }
        return $result;
    }

    /**
     * @param Attribute[]|Collection $attributes
     * @return array
     */
    protected function getAttributeNames($attributes)
    {
        $attributeNames = array();
        if ($attributes) {
            foreach ($attributes as $attribute) {
                $attributeNames[] = $attribute->getName();
            }
        }

        return $attributeNames;
    }
}
