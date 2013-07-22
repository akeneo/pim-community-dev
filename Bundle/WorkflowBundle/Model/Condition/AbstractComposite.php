<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

use Oro\Bundle\WorkflowBundle\Exception\ConditionInitializationException;

abstract class AbstractComposite implements ConditionInterface
{
    /**
     * @var ConditionInterface[]
     */
    protected $conditions = array();

    /**
     * Initialize composite conditions
     *
     * @param array $options
     * @return AbstractComposite
     * @throws ConditionInitializationException
     */
    public function initialize(array $options)
    {
        if (!$options) {
            throw new ConditionInitializationException('Options must have at least one element');
        }

        $this->conditions = array();

        foreach ($options as $condition) {
            if ($condition instanceof ConditionInterface) {
                $this->add($condition);
            } else {
                throw new ConditionInitializationException(
                    sprintf(
                        'Options must contain an instance of %s, %s is given',
                        'Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface',
                        is_object($condition) ? get_class($condition) : gettype($condition)
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
