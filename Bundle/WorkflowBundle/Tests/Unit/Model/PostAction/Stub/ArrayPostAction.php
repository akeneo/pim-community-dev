<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\PostAction\Stub;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException;
use Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionInterface;
use Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface;

class ArrayPostAction extends ArrayCollection implements PostActionInterface
{
    /**
     * @var ConditionInterface
     */
    protected $condition;

    /**
     * Do nothing
     *
     * @param mixed $context
     */
    public function execute($context)
    {
    }

    /**
     * @param array $options
     * @return PostActionInterface
     * @throws InvalidParameterException
     */
    public function initialize(array $options)
    {
        $this->set('parameters', $options);
        return $this;
    }

    /**
     * @param ConditionInterface $condition
     * @return mixed
     */
    public function setCondition(ConditionInterface $condition)
    {
        $this->condition = $condition;
    }

    /**
     * @return ConditionInterface
     */
    public function getCondition()
    {
        return $this->condition;
    }
}
