<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

class TrueCondition implements ConditionInterface
{
    /**
     * Always return TRUE
     *
     * @param mixed $context
     * @return boolean
     */
    public function isAllowed($context)
    {
        return true;
    }

    /**
     * Nothing to initialize
     *
     * @param array $options
     * @return TrueCondition
     */
    public function initialize(array $options)
    {
        return $this;
    }
}
