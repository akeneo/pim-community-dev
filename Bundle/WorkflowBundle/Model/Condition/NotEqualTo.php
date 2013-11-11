<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

class NotEqualTo extends AbstractCondition
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
    protected function isConditionAllowed($context)
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
