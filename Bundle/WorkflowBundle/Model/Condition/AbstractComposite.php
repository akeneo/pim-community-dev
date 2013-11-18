<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

use Oro\Bundle\WorkflowBundle\Exception\ConditionException;

abstract class AbstractComposite extends AbstractCondition
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
     * @throws ConditionException
     */
    public function initialize(array $options)
    {
        if (!$options) {
            throw new ConditionException('Options must have at least one element');
        }

        $this->conditions = array();

        foreach ($options as $condition) {
            if ($condition instanceof ConditionInterface) {
                $this->add($condition);
            } else {
                throw new ConditionException(
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
