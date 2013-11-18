<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Action\Stub;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException;
use Oro\Bundle\WorkflowBundle\Model\Action\ActionInterface;
use Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface;

class ArrayAction extends ArrayCollection implements ActionInterface
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
     * @return ActionInterface
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
