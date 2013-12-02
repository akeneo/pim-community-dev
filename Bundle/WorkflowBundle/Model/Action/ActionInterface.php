<?php

namespace Oro\Bundle\WorkflowBundle\Model\Action;

use Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException;
use Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface;

interface ActionInterface
{
    /**
     * Execute action.
     *
     * @param mixed $context
     */
    public function execute($context);

    /**
     * Initialize action based on passed options.
     *
     * @param array $options
     * @return ActionInterface
     * @throws InvalidParameterException
     */
    public function initialize(array $options);

    /**
     * Set optional condition for action
     *
     * @param ConditionInterface $condition
     * @return mixed
     */
    public function setCondition(ConditionInterface $condition);
}
