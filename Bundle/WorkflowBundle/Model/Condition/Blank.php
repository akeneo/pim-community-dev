<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

use Oro\Bundle\WorkflowBundle\Exception\ConditionInitializationException;
use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;

class Blank implements ConditionInterface
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
     * Returns TRUE is target is empty in context
     *
     * @param mixed $context
     * @return boolean
     */
    public function isAllowed($context)
    {
        $value = $this->contextAccessor->getValue($context, $this->target);
        return '' === $value || null === $value;
    }

    /**
     * Initialize target that will be checked for emptiness
     *
     * @param array $options
     * @return Blank
     * @throws ConditionInitializationException
     */
    public function initialize(array $options)
    {
        if (1 == count($options)) {
            $this->target = reset($options);
        } else {
            throw new ConditionInitializationException(
                sprintf(
                    'Options must have 1 element, but %d given',
                    count($options)
                )
            );
        }
    }
}
