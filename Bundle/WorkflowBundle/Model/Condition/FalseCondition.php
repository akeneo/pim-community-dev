<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

class FalseCondition implements ConditionInterface
{
    /**
     * Always return FALSE
     *
     * @param mixed $context
     * @return boolean
     */
    public function isAllowed($context)
    {
        return false;
    }

    /**
     * Nothing to initialize
     *
     * @param array $options
     * @return FalseCondition
     */
    public function initialize(array $options)
    {
        return $this;
    }
}
