<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

abstract class AbstractCompositeCondition implements ConditionInterface
{
    /**
     * @var ConditionInterface[]
     */
    protected $conditions;

    /**
     * Initialize composite conditions
     *
     * @param array $options
     * @throws \InvalidArgumentException
     */
    public function initialize(array $options)
    {
        if (!$options) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Argument $options must have at least one element',
                    'Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface'
                )
            );
        }

        $this->conditions = array();

        foreach ($options as $condition) {
            if ($condition instanceof ConditionInterface) {
                $this->add($condition);
            } else {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Element of argument $options must be an instance of %s',
                        'Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface'
                    )
                );
            }
        }
    }

    /**
     * @param ConditionInterface $condition
     */
    public function add(ConditionInterface $condition)
    {
        $this->conditions[] = $condition;
    }
}
