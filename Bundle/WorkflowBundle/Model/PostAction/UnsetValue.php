<?php

namespace Oro\Bundle\WorkflowBundle\Model\PostAction;

use Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException;
use Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface;

class UnsetValue implements PostActionInterface
{
    /**
     * @var AssignValue
     */
    protected $assignValueAction;

    /**
     * @param AssignValue $assignValueAction
     */
    public function __construct(AssignValue $assignValueAction)
    {
        $this->assignValueAction = $assignValueAction;
    }

    /**
     * Execute post action.
     *
     * @param mixed $context
     */
    public function execute($context)
    {
        $this->assignValueAction->execute($context);
    }

    /**
     * Initialize post action based on passed options.
     *
     * @param array $options
     * @return PostActionInterface
     * @throws InvalidParameterException
     */
    public function initialize(array $options)
    {
        if (!isset($options['attribute']) && isset($options[0])) {
            $options[1] = null;
        } else {
            $options['value'] = null;
        }
        $this->assignValueAction->initialize($options);

        return $this;
    }

    /**
     * Set optional condition for post action
     *
     * @param ConditionInterface $condition
     * @return mixed
     */
    public function setCondition(ConditionInterface $condition)
    {
        $this->assignValueAction->setCondition($condition);
    }
}
