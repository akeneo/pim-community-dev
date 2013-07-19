<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;

class OrCondition extends AbstractCompositeCondition
{
    /**
     * Check at lest one condition meets WorkflowItem..
     *
     * @param WorkflowItem $workflowItem
     * @return boolean
     */
    public function isAllowed(WorkflowItem $workflowItem)
    {
        foreach ($this->conditions as $condition) {
            if ($condition->isAllowed($workflowItem)) {
                return true;
            }
        }
        return false;
    }
}
