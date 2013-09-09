<?php

namespace Oro\Bundle\WorkflowBundle\Exception;

class WorkflowNotFoundException extends WorkflowException
{
    public function __construct($name)
    {
        parent::__construct(sprintf('Workflow "%s" not found', $name));
    }
}
