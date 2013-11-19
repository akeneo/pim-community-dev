<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

use Doctrine\Common\Collections\Collection;

use Oro\Bundle\WorkflowBundle\Exception\ConditionException;

class Not extends AbstractCondition
{
    /**
     * @var ConditionInterface
     */
    protected $condition;

    /**
     * Returns negation of embedded condition
     *
     * @param mixed $context
     * @param Collection|null $errors
     * @return boolean
     */
    public function isAllowed($context, Collection $errors = null)
    {
        $isAllowed = !$this->condition->isAllowed($context, $errors);
        if (!$isAllowed) {
            $this->addError($context, $errors);
        }

        return $isAllowed;
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
