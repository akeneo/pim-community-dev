<?php

namespace Oro\Bundle\WorkflowBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;

use Oro\Bundle\WorkflowBundle\Model\Step;
use Oro\Bundle\WorkflowBundle\Model\Attribute;
use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Exception\UnknownStepException;
use Oro\Bundle\WorkflowBundle\Exception\UnknownAttributeException;
use Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException;

class OroWorkflowStep extends AbstractType
{
    const NAME = 'oro_workflow_step';

    /**
     * @var WorkflowRegistry
     */
    protected $workflowRegistry;

    /**
     * @param WorkflowRegistry $workflowRegistry
     */
    public function __construct(WorkflowRegistry $workflowRegistry)
    {
        $this->workflowRegistry = $workflowRegistry;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @throws UnknownAttributeException
     * @throws InvalidParameterException
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var WorkflowItem $workflowItem */
        $workflowItem = $options['workflowItem'];
        $workflow = $this->workflowRegistry->getWorkflow($workflowItem->getWorkflowName());
        /** @var Step $step */
        $step = $workflow->getStep($options['stepName']);

        $stepFormOptions = $step->getFormOptions();
        if (!empty($stepFormOptions['attribute_fields'])) {
            foreach ($stepFormOptions['attribute_fields'] as $attributeName => $attributeOptions) {
                $attribute = $workflow->getAttributes()->get($attributeName);
                $this->addAttributeField($builder, $attribute, $attributeOptions, $workflowItem, $step);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * Adds form type of attribute to form builder
     *
     * @param FormBuilderInterface $builder
     * @param Attribute $attribute
     * @param array $attributeOptions
     * @param WorkflowItem $workflowItem
     * @param Step $step
     */
    protected function addAttributeField(
        FormBuilderInterface $builder,
        Attribute $attribute,
        array $attributeOptions,
        WorkflowItem $workflowItem,
        Step $step
    ) {
        $attributeOptions = $this->prepareAttributeOptions($attribute, $attributeOptions, $workflowItem, $step);
        $builder->add($attribute->getName(), $attributeOptions['form_type'], $attributeOptions['options']);
    }

    /**
     * Prepares options of attribute need to add corresponding form type
     *
     * @param Attribute $attribute
     * @param array $attributeOptions
     * @param WorkflowItem $workflowItem
     * @param Step $step
     * @return array
     * @throws \Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException
     */
    protected function prepareAttributeOptions(
        Attribute $attribute,
        array $attributeOptions,
        WorkflowItem $workflowItem,
        Step $step
    ) {
        // ensure has form_type
        if (empty($attributeOptions['form_type'])) {
            throw new InvalidParameterException(
                sprintf(
                    'Parameter "form_type" must be defined for attribute "%s" in workflow "%s"',
                    $attribute->getName(),
                    $workflowItem->getWorkflowName()
                )
            );
        }

        // updates form options
        if (!isset($attributeOptions['options'])) {
            $attributeOptions['options'] = array();
        }

        // updates form options label
        if (!isset($attributeOptions['options']['label'])) {
            $attributeOptions['options']['label'] = isset($attributeOptions['label'])
                ? $attributeOptions['options']['label']
                : $attribute->getLabel();
        }

        // disable field if current step of workflow item
        if ($step->getName() !== $workflowItem->getCurrentStepName() || $workflowItem->isClosed()) {
            $attributeOptions['options']['disabled'] = true;
        }

        return $attributeOptions;
    }

    /**
     * Custom options:
     * - "workflow" - required, instance of current Workflow entity
     * - "step"     - required, instance of current Step entity
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('workflowItem'));

        $resolver->setDefaults(
            array(
                'data_class' => 'Oro\Bundle\WorkflowBundle\Model\WorkflowData',
                'stepName' => null
            )
        );

        $workflowRegistry = $this->workflowRegistry;

        $resolver->setNormalizers(
            array(
                'workflowItem' => function (Options $options, $workflowItem) {
                    if (!$workflowItem instanceof WorkflowItem) {
                        throw new UnexpectedTypeException(
                            $workflowItem,
                            'Oro\Bundle\WorkflowBundle\Entity\WorkflowItem'
                        );
                    }

                    return $workflowItem;
                },
                'stepName' => function (Options $options, $stepName) use ($workflowRegistry) {
                    /** @var WorkflowItem $workflowItem */
                    $workflowItem = $options['workflowItem'];

                    if (!$stepName) {
                        $stepName = $workflowItem->getCurrentStepName();
                    }

                    $workflow = $workflowRegistry->getWorkflow($workflowItem->getWorkflowName());
                    if (!$workflow->getStep($stepName)) {
                        throw new UnknownStepException($stepName);
                    }

                    return $stepName;
                },
            )
        );
    }
}
