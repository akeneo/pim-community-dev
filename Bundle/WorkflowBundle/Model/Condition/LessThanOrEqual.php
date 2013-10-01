<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

class LessThanOrEqual extends AbstractComparison
{
    /**
     * Checks that left values is less or equal than right value
     *
     * @param mixed $left
     * @param mixed $right
     * @return boolean
     */
    protected function doCompare($left, $right)
    {
        return $left <= $right;
    }
}
