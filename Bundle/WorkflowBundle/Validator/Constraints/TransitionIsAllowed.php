<?php

namespace Oro\Bundle\WorkflowBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;

class TransitionIsAllowed extends Constraint
{
    /**
     * @var WorkflowItem
     */
    protected $workflowItem;

    /**
     * @var string
     */
    protected $transitionName;

    public $unknownTransitionMessage = '"Transition {{ transition }}" is not exist in workflow.';
    public $notStartTransitionMessage = '"{{ transition }}" is not start transition.';
    public $stepHasNotAllowedTransitionMessage = '"{{ transition }}" transition is not allowed at step "{{ step }}".';
    public $someConditionsNotMetMessage = 'Some transition conditions are not met.';

    /**
     * @param WorkflowItem $workflowItem
     * @param string $transitionName
     */
    public function __construct(WorkflowItem $workflowItem, $transitionName)
    {
        $this->workflowItem = $workflowItem;
        $this->transitionName = $transitionName;
    }

    /**
     * @return WorkflowItem
     */
    public function getWorkflowItem()
    {
        return $this->workflowItem;
    }

    /**
     * @return string
     */
    public function getTransitionName()
    {
        return $this->transitionName;
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return TransitionIsAllowedValidator::ALIAS;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
