<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

class LessThanCondition extends CompareCondition
{
    /**
     * Checks that left values is less than right value
     *
     * @param mixed $left
     * @param mixed $right
     * @return boolean
     */
    protected function doCompare($left, $right)
    {
        return $left < $right;
    }
}
