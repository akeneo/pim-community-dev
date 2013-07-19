<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

use Oro\Bundle\WorkflowBundle\Exception\ConditionInitializationException;

abstract class AbstractCompositeCondition implements ConditionInterface
{
    /**
     * @var ConditionInterface[]
     */
    protected $conditions = array();

    /**
     * Initialize composite conditions
     *
     * @param array $options
     * @return AbstractCompositeCondition
     * @throws ConditionInitializationException
     */
    public function initialize(array $options)
    {
        if (!$options) {
            throw new ConditionInitializationException('Argument $options must have at least one element');
        }

        $this->conditions = array();

        foreach ($options as $condition) {
            if ($condition instanceof ConditionInterface) {
                $this->add($condition);
            } else {
                throw new ConditionInitializationException(
                    sprintf(
                        'Element of argument $options must be an instance of %s',
                        'Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface'
                    )
                );
            }
        }
    }

    /**
     * Add condition to composite
     *
     * @param ConditionInterface $condition
     */
    public function add(ConditionInterface $condition)
    {
        $this->conditions[] = $condition;
    }
}
