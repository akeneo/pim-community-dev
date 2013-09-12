<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

class Andx extends AbstractComposite
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
            return false;
        }
        foreach ($this->conditions as $condition) {
            if (!$condition->isAllowed($context)) {
                return false;
            }
        }
        return true;
    }
}
