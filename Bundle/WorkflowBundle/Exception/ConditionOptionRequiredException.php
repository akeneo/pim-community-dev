<?php

namespace Oro\Bundle\WorkflowBundle\Exception;

class ConditionOptionRequiredException extends ConditionException
{
    public function __construct($optionName)
    {
        parent::__construct(sprintf('Condition option "%s" is required.', $optionName));
    }
}
