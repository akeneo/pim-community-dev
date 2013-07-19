<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;

interface ConditionInterface
{
    /**
     * Check if workflow item meets condition requirements.
     *
     * @param WorkflowItem $workflowItem
     * @return boolean
     */
    public function isAllowed(WorkflowItem $workflowItem);

    /**
     * Initialize condition options
     *
     * @param array $options
     */
    public function initialize(array $options);
}
