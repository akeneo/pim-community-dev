<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

use Oro\Bundle\WorkflowBundle\Exception\ConditionException;
use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;

class HasValue extends AbstractCondition
{
    /**
     * @var ContextAccessor
     */
    protected $contextAccessor;

    /**
     * @var mixed
     */
    protected $target;

    /**
     * Constructor
     *
     * @param ContextAccessor $contextAccessor
     */
    public function __construct(ContextAccessor $contextAccessor)
    {
        $this->contextAccessor = $contextAccessor;
    }

    /**
     * Check if values equals.
     *
     * @param mixed $context
     * @return boolean
     */
    protected function isConditionAllowed($context)
    {
        return $this->contextAccessor->hasValue($context, $this->target);
    }

    /**
     * Initialize target that will be checked for emptiness
     *
     * @param array $options
     * @return HasValue
     * @throws ConditionException
     */
    public function initialize(array $options)
    {
        if (1 == count($options)) {
            $this->target = reset($options);
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
