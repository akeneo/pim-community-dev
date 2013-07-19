<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

use Oro\Bundle\WorkflowBundle\Model\Condition\EqualToCondition;

class NotEqualToCondition implements ConditionInterface
{
    /**
     * @var EqualToCondition
     */
    protected $equalToCondition;

    /**
     * Constructor
     *
     * @param EqualToCondition $equalToCondition
     */
    public function __construct(EqualToCondition $equalToCondition)
    {
        $this->equalToCondition = $equalToCondition;
    }

    /**
     * Check if values equals.
     *
     * @param mixed $context
     * @return boolean
     */
    public function isAllowed($context)
    {
        return !$this->equalToCondition->isAllowed($context);
    }

    /**
     * Initialize condition options
     *
     * @param array $options
     * @return NotEqualToCondition
     */
    public function initialize(array $options)
    {
        $this->equalToCondition->initialize($options);

        return $this;
    }
}
