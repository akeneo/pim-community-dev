<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

class GreaterThan extends AbstractComparison
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
