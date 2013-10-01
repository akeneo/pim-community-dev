<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

use Oro\Bundle\WorkflowBundle\Exception\ConditionException;

class Not implements ConditionInterface
{
    /**
     * @var ConditionInterface
     */
    protected $condition;

    /**
     * Returns negation of embedded condition
     *
     * @param mixed $context
     * @return boolean
     */
    public function isAllowed($context)
    {
        return !$this->condition->isAllowed($context);
    }

    /**
     * Initialize condition that will be negated
     *
     * @param array $options
     * @return Not
     * @throws ConditionException
     */
    public function initialize(array $options)
    {
        if (1 == count($options)) {
            $condition = reset($options);
            if ($condition instanceof ConditionInterface) {
                $this->condition = $condition;
            } else {
                throw new ConditionException(
                    sprintf(
                        'Options must contain an instance of %s',
                        'Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface'
                    )
                );
            }
        } else {
            throw new ConditionException(
                sprintf(
                    'Options must have 1 element, but %d given',
                    count($options)
                )
            );
        }
    }
}
