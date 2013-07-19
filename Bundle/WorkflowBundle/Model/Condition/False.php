<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

use Oro\Bundle\WorkflowBundle\Exception\ConditionInitializationException;

class False implements ConditionInterface
{
    /**
     * Always return FALSE
     *
     * @param mixed $context
     * @return boolean
     */
    public function isAllowed($context)
    {
        return false;
    }

    /**
     * Nothing to initialize
     *
     * @param array $options
     * @return True
     * @throws ConditionInitializationException If options passed
     */
    public function initialize(array $options)
    {
        if (!empty($options)) {
            throw new ConditionInitializationException('Options are prohibited');
        }
        return $this;
    }
}
