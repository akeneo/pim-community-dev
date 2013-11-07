<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

abstract class AbstractCondition implements ConditionInterface
{
    /**
     * @var string
     */
    protected $message;

    /**
     * {@inheritdoc}
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        return $this->message;
    }
}
