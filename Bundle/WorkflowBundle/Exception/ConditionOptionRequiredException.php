<?php

namespace Oro\Bundle\WorkflowBundle\Exception;

class ConditionOptionRequiredException extends ConditionException
{
    public function __construct($optionName)
    {
        parent::__construct(sprintf('Option "%s" is required.', $optionName));
    }
}
