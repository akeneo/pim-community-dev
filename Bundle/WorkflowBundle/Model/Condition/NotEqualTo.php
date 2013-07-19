<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

use Oro\Bundle\WorkflowBundle\Model\Condition\EqualTo;

class NotEqualTo implements ConditionInterface
{
    /**
     * @var EqualTo
     */
    protected $equalToCondition;

    /**
     * Constructor
     *
     * @param EqualTo $equalToCondition
     */
    public function __construct(EqualTo $equalToCondition)
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
     * @return NotEqualTo
     */
    public function initialize(array $options)
    {
        $this->equalToCondition->initialize($options);

        return $this;
    }
}
