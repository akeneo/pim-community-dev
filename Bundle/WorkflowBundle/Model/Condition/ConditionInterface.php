<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Exception\ConditionInitializationException;

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
     * @throws ConditionInitializationException If initialization fails
     */
    public function initialize(array $options);
}
