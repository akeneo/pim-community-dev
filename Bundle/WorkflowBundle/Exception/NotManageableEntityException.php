<?php

namespace Oro\Bundle\WorkflowBundle\Exception;

class NotManageableEntityException extends WorkflowException
{
    public function __construct($className)
    {
        parent::__construct(sprintf('Entity class "%s" is not manageable.', $className));
    }
}
