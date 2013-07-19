<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

class GreaterThanCondition extends CompareCondition
{
    /**
     * Checks that left values is greater than right value
     *
     * @param mixed $left
     * @param mixed $right
     * @return boolean
     */
    protected function doCompare($left, $right)
    {
        return $left > $right;
    }
}
