<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\PostAction\Stub;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\WorkflowBundle\Exception\ConditionException;
use Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface;

class ArrayCondition extends ArrayCollection implements ConditionInterface
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

    /**
     * @param array $options
     * @return ConditionInterface
     * @throws ConditionException
     */
    public function initialize(array $options)
    {
        foreach ($options as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Check if context meets condition requirements.
     *
     * @param mixed $context
     * @return boolean
     */
    public function isAllowed($context)
    {
        $isAllowed = $this->get('allowed');

        return $isAllowed ? true : false;
    }
}
