<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

use Oro\Bundle\WorkflowBundle\Exception\ConditionInitializationException;

class NotCondition implements ConditionInterface
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
        return !$this->isAllowed($context);
    }

    /**
     * Nothing to initialize
     *
     * @param array $options
     * @return TrueCondition
     * @throws ConditionInitializationException
     */
    public function initialize(array $options)
    {
        if (1 == count($options)) {
            $condition = reset($options);
            if ($condition instanceof ConditionInterface) {
                $this->condition = $condition;
            } else {
                throw new ConditionInitializationException(
                    sprintf(
                        'Element of argument $options must be an instance of %s',
                        'Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface'
                    )
                );
            }
        } else {
            throw new ConditionInitializationException(
                sprintf(
                    'One element in $options argument is expected, %d given',
                    count($options)
                )
            );
        }
    }
}
