<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

class LessThan extends AbstractComparison
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
