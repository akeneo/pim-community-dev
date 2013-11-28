<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

class NotHasValue extends AbstractCondition
{
    /**
     * @var HasValue
     */
    protected $hasValueCondition;

    /**
     * Constructor
     *
     * @param HasValue $hasValueCondition
     */
    public function __construct(HasValue $hasValueCondition)
    {
        $this->hasValueCondition = $hasValueCondition;
    }

    /**
     * Check if values equals.
     *
     * @param mixed $context
     * @return boolean
     */
    protected function isConditionAllowed($context)
    {
        return !$this->hasValueCondition->isAllowed($context);
    }

    /**
     * Initialize condition options
     *
     * @param array $options
     * @return HasValue
     */
    public function initialize(array $options)
    {
        $this->hasValueCondition->initialize($options);

        return $this;
    }
}
