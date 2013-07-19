<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

class OrCondition extends AbstractCompositeCondition
{
    /**
     * Check at lest one condition meets context.
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
            if ($condition->isAllowed($context)) {
                return true;
            }
        }
        return false;
    }
}
