<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

class Orx extends AbstractComposite
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
