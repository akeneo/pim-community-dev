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
     * @var Attribute[]
     */
    protected $attributes;

    /**
     * @var FormOptionsAssembler
     */
    protected $formOptionsAssembler;

    /**
     * @param FormOptionsAssembler $formOptionsAssembler
     */
    public function __construct(FormOptionsAssembler $formOptionsAssembler)
    {
        $this->formOptionsAssembler = $formOptionsAssembler;
    }

    /**
     * @param array $configuration
     * @param Attribute[]|Collection $attributes
     * @return ArrayCollection
     */
    public function assemble(array $configuration, $attributes)
    {
        $this->setAttributes($attributes);

        $steps = new ArrayCollection();
        foreach ($configuration as $name => $options) {
            $step = $this->assembleStep($name, $options);
            $steps->set($name, $step);
        }

        $this->attributes = array();

        return $steps;
    }

    /**
     * @param string $name
     * @param array $options
     * @return Step
     * @throws InvalidParameterException
     * @throws UnknownAttributeException
     */
    protected function assembleStep($name, array $options)
    {
        $this->assertOptions($options, array('label'));

        $step = new Step();
        $step->setName($name)
            ->setLabel($options['label'])
            ->setTemplate($this->getOption($options, 'template', null))
            ->setOrder($this->getOption($options, 'order', 0))
            ->setIsFinal($this->getOption($options, 'is_final', false))
            ->setAllowedTransitions($this->getOption($options, 'allowed_transitions', array()))
            ->setFormType($this->getOption($options, 'form_type', WorkflowStepType::NAME))
            ->setFormOptions($this->assembleFormOptions($options, $name))
            ->setViewAttributes($this->assembleViewAttributes($options, $name));

        return $step;
    }

    /**
     * @param array $options
     * @param string $stepName
     * @return array
     */
    protected function assembleFormOptions(array $options, $stepName)
    {
        $formOptions = $this->getOption($options, 'form_options', array());
        return $this->formOptionsAssembler->assemble($formOptions, $this->attributes, 'step', $stepName);
    }

    /**
     * @param array $options
     * @param string $stepName
     * @return array
     * @throws InvalidParameterException
     */
    protected function assembleViewAttributes(array $options, $stepName)
    {
        $viewAttributes = $this->getOption($options, 'view_attributes', array());

        if (!is_array($viewAttributes)) {
            throw new InvalidParameterException(
                sprintf('Option "view_attributes" at step "%s" must be an array', $stepName)
            );
        }

        $result = array();
        foreach ($viewAttributes as $index => $viewAttribute) {
            if (isset($viewAttribute['attribute'])) {
                $attributeName = $viewAttribute['attribute'];
                $this->assertAttributeExists($attributeName, $stepName);
                if (!isset($viewAttribute['path'])) {
                    $viewAttribute['path'] = '$' . $viewAttribute['attribute'];
                }
                if (!isset($viewAttribute['label'])) {
                    $viewAttribute['label'] = $this->attributes[$viewAttribute['attribute']]->getLabel();
                }
            } elseif (!isset($viewAttribute['path'])) {
                throw new InvalidParameterException(
                    sprintf(
                        'Option "path" or "attribute" at view attribute "%s" of step "%s" is required',
                        $index,
                        $stepName
                    )
                );
            } elseif (!isset($viewAttribute['label'])) {
                throw new InvalidParameterException(
                    sprintf(
                        'Option "label" at view attribute "%s" of step "%s" is required',
                        $index,
                        $stepName
                    )
                );
            }
            $result[] = $this->passConfiguration($viewAttribute);
        }
        return $result;
    }

    /**
     * @param Attribute[]|Collection $attributes
     * @return array
     */
    protected function setAttributes($attributes)
    {
        $this->attributes = array();
        if ($attributes) {
            foreach ($attributes as $attribute) {
                $this->attributes[$attribute->getName()] = $attribute;
            }
        }
    }

    /**
     * @param array $attributeNames
     * @param string $stepName
     * @throws UnknownAttributeException
     */
    protected function assertAttributesExist(array $attributeNames, $stepName)
    {
        foreach ($attributeNames as $attributeName) {
            $this->assertAttributeExists($attributeName, $stepName);
        }
    }

    /**
     * @param string $attributeName
     * @param string $stepName
     * @throws UnknownAttributeException
     */
    protected function assertAttributeExists($attributeName, $stepName)
    {
        if (!isset($this->attributes[$attributeName])) {
            throw new UnknownAttributeException(
                sprintf('Unknown attribute "%s" at step "%s"', $attributeName, $stepName)
            );
        }
    }
}
