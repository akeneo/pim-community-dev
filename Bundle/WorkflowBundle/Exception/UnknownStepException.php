<?php

namespace Oro\Bundle\WorkflowBundle\Exception;

class UnknownStepException extends WorkflowException
{
    public function __construct($stepName, $workflowName)
    {
        parent::__construct(sprintf('Step "%s" of workflow "%s" not found', $stepName, $workflowName));
    }
}
