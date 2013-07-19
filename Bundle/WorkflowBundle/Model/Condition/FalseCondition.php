<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;

class FalseCondition implements ConditionInterface
{
    /**
     * Always return FALSE
     *
     * @param WorkflowItem $workflowItem
     * @return boolean
     */
    public function isAllowed(WorkflowItem $workflowItem)
    {
        return false;
    }

    /**
     * Nothing to initialize
     *
     * @param array $options
     */
    public function initialize(array $options)
    {

    }
}
