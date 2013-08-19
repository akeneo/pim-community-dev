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
use Oro\Bundle\WorkflowBundle\Exception\UnknownStepException;
use Oro\Bundle\WorkflowBundle\Exception\UnknownAttributeException;
use Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException;

class OroWorkflowStep extends AbstractType
{
    const NAME = 'oro_workflow_step';

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @throws UnknownAttributeException
     * @throws InvalidParameterException
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Workflow $workflow */
        $workflow = $options['workflow'];
        /** @var Step $step */
        $step = $options['step'];

        $stepFormOptions = $step->getFormOptions();
        if (empty($stepFormOptions['attribute_fields'])) {
            return;
        }

        foreach ($stepFormOptions['attribute_fields'] as $attributeName => $attributeOptions) {
            /** @var Attribute $attribute */
            $attribute = $workflow->getAttributes()->get($attributeName);
            if (!$attribute) {
                throw new UnknownAttributeException(
                    sprintf('Unknown attribute "%s" in workflow "%s"', $attributeName, $workflow->getName())
                );
            }

            // get form type
            if (empty($attributeOptions['form_type'])) {
                throw new InvalidParameterException(
                    sprintf(
                        'Parameter "form_type" must be defined for attribute "%s" in workflow "%s"',
                        $attributeName,
                        $workflow->getName()
                    )
                );
            }
            $formType = $attributeOptions['form_type'];

            // get form options
            $formOptions = array();
            if (isset($attributeOptions['options'])) {
                $formOptions = $attributeOptions['options'];
            }
            if (!isset($formOptions['label'])) {
                $formOptions['label'] = isset($attributeOptions['label'])
                    ? $attributeOptions['label']
                    : $attribute->getLabel();
            }

            $builder->add($attributeName, $formType, $formOptions);
        }
    }

    /**
     * @param Attribute $attribute
     * @return array
     */
    protected function getAttributeFormOptions(Attribute $attribute)
    {
        $formOptions = $attribute->getOption('form_options');
        $formOptions = $formOptions ? $formOptions : array();
        $formOptions['label'] = $attribute->getLabel();

        return $formOptions;
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
        $resolver->setRequired(array('workflow', 'step'));

        $resolver->setDefaults(
            array(
                'data_class' => 'Oro\Bundle\WorkflowBundle\Model\WorkflowData',
                'attributes' => array(),
            )
        );

        $resolver->setNormalizers(
            array(
                'workflow' => function (Options $options, $workflow) {
                    if (!$workflow instanceof Workflow) {
                        throw new UnexpectedTypeException($workflow, 'Oro\Bundle\WorkflowBundle\Model\Workflow');
                    }

                    return $workflow;
                },
                'step' => function (Options $options, $step) {
                    if (!$step instanceof Step) {
                        throw new UnexpectedTypeException($step, 'Oro\Bundle\WorkflowBundle\Model\Step');
                    }

                    /** @var Workflow $workflow */
                    $workflow = $options['workflow'];
                    if (!$workflow->getSteps()->contains($step)) {
                        throw new UnknownStepException($step->getName());
                    }

                    return $step;
                },
            )
        );
    }
}
