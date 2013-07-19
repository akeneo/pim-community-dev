<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

use Oro\Bundle\WorkflowBundle\Exception\ConditionInitializationException;

class True implements ConditionInterface
{
    /**
     * Always return TRUE
     *
     * @param mixed $context
     * @return boolean
     */
    public function isAllowed($context)
    {
        return true;
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
