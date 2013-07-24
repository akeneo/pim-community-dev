<?php

namespace Oro\Bundle\WorkflowBundle\Exception;

class UnknownStepException extends WorkflowException
{
    public function __construct($stepName)
    {
        parent::__construct(sprintf('Step "%s" not found', $stepName));
    }
}
