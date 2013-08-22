<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

use Oro\Bundle\WorkflowBundle\Exception\ConditionException;

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
     * @throws ConditionException If options passed
     */
    public function initialize(array $options)
    {
        if (!empty($options)) {
            throw new ConditionException('Options are prohibited');
        }
        return $this;
    }
}
