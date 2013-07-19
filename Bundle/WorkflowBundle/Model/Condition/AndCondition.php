<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;

class AndCondition extends AbstractCompositeCondition
{
    /**
     * Check if all conditions meets WorkflowItem.
     *
     * @param WorkflowItem $workflowItem
     * @return boolean
     */
    public function isAllowed(WorkflowItem $workflowItem)
    {
        foreach ($this->conditions as $condition) {
            if (!$condition->isAllowed($workflowItem)) {
                return false;
            }
        }
        return true;
    }
}
