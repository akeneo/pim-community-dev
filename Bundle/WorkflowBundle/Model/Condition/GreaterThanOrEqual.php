<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

class GreaterThanOrEqual extends AbstractComparison
{
    /**
     * Checks that left values is greater or equal than right value
     *
     * @param mixed $left
     * @param mixed $right
     * @return boolean
     */
    protected function doCompare($left, $right)
    {
        return $left >= $right;
    }
}
