<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Action\Stub;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

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
     * {@inheritDoc}
     */
    public function isAllowed($context, Collection $errors = null)
    {
        $isAllowed = $this->get('allowed');

        return $isAllowed ? true : false;
    }
}
