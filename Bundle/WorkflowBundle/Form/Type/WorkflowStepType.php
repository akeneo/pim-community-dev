<?php

namespace Oro\Bundle\WorkflowBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;

use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;

class WorkflowStepType extends AbstractType
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
     * {@inheritDoc}
     */
    public function getParent()
    {
        return WorkflowAttributesType::NAME;
    }

    /**
     * Custom options:
     * - "workflow_item" - required, instance of WorkflowItem entity
     * - "step_name"     - optional, name of step
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('workflow_item'));
        $resolver->setOptional(array('step_name'));

        $resolver->setAllowedTypes(
            array(
                'step_name' => 'string',
            )
        );

        $resolver->setNormalizers(
            array(
                'step_name' => function (Options $options, $stepName) {
                    /** @var Workflow $workflow */
                    $workflow = $options['workflow'];

                    /** @var WorkflowItem $workflowItem */
                    $workflowItem = $options['workflow_item'];

                    if (!$stepName) {
                        $stepName = $workflowItem->getCurrentStepName();
                    }

                    if (!$workflow->getStepManager()->getStep($stepName)) {
                        throw new InvalidConfigurationException(
                            sprintf(
                                'Invalid reference to unknown step "%s" of workflow "%s".',
                                $stepName,
                                $workflow->getName()
                            )
                        );
                    }

                    return $stepName;
                },
                'disable_attribute_fields' => function (Options $options, $disableAttributeFields) {
                    /** @var Workflow $workflow */
                    $workflow = $options['workflow'];
                    /** @var WorkflowItem $workflowItem */
                    $workflowItem = $options['workflow_item'];

                    $step = $workflow->getStepManager()->getStep($options['step_name']);

                    if ($step->getName() !== $workflowItem->getCurrentStepName() || $workflowItem->isClosed()) {
                        $disableAttributeFields = true;
                    }

                    return $disableAttributeFields;
                }
            )
        );
    }
}
