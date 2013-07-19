<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

class AndCondition extends AbstractCompositeCondition
{
    /**
     * Check if all conditions meets context.
     *
     * @param mixed $context
     * @return boolean
     */
    public function isAllowed($context)
    {
        if (!$this->conditions) {
            return true;
        }
        foreach ($this->conditions as $condition) {
            if (!$condition->isAllowed($context)) {
                return false;
            }
        }
        return true;
    }
}
