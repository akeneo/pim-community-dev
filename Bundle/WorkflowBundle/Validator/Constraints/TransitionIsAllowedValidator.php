<?php

namespace Oro\Bundle\WorkflowBundle\Validator\Constraints;

use Oro\Bundle\WorkflowBundle\Exception\InvalidTransitionException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowData;
use Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry;

class TransitionIsAllowedValidator extends ConstraintValidator
{
    const ALIAS = 'oro_workflow_transition_is_allowed';

    /**
     * @var WorkflowRegistry
     */
    protected $registry;

    /**
     * @param WorkflowRegistry $registry
     */
    public function __construct(WorkflowRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Checks if current workflow item allows transition
     *
     * @param WorkflowData $value
     * @param TransitionIsAllowed $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        /** @var WorkflowItem $workflowItem */
        $workflowItem = $constraint->getWorkflowItem();
        $transitionName = $constraint->getTransitionName();
        $workflow = $this->registry->getWorkflow($workflowItem->getWorkflowName());

        $errors = new ArrayCollection();

        $result = false;
        try {
            $result = $workflow->isTransitionAllowed($workflowItem, $constraint->getTransitionName(), $errors, true);
        } catch (InvalidTransitionException $e) {
            switch ($e->getCode()) {
                case InvalidTransitionException::UNKNOWN_TRANSITION:
                    $errors->add(
                        array(
                            $constraint->unknownTransitionMessage,
                            array('{{ transition }}' => $transitionName)
                        )
                    );
                    break;
                case InvalidTransitionException::NOT_START_TRANSITION:
                    $errors->add(
                        array(
                            $constraint->notStartTransitionMessage,
                            array('{{ transition }}' => $transitionName)
                        )
                    );
                    break;
                case InvalidTransitionException::STEP_HAS_NO_ALLOWED_TRANSITION:
                    $errors->add(
                        array(
                            $constraint->stepHasNotAllowedTransitionMessage,
                            array(
                                '{{ transition }}' => $transitionName,
                                '{{ step }}' => $workflowItem->getCurrentStepName()
                            )
                        )
                    );
                    break;
            }
        }

        if (!$result) {
            if ($errors->count()) {
                foreach ($errors as $errorMessage) {
                    $params = array();
                    if (is_array($errorMessage)) {
                        list($errorMessage, $params) = array_values($errorMessage);
                    }
                    $this->context->addViolation($errorMessage, $params);
                }
            } else {
                $this->context->addViolation($constraint->someConditionsNotMetMessage);
            }
        }
    }
}
