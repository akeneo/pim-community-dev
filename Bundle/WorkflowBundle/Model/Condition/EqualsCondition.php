<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Exception\ConditionOptionRequiredException;

class EqualsCondition implements ConditionInterface
{
    /**
     * @var string
     */
    protected $left;

    /**
     * @var string
     */
    protected $right;

    /**
     * Check if values equals.
     *
     * @param WorkflowItem $workflowItem
     * @return boolean
     */
    public function isAllowed(WorkflowItem $workflowItem)
    {
        // @TODO Implement condition
        return true;
    }

    /**
     * Initialize condition options
     *
     * @param array $options
     */
    public function initialize(array $options)
    {
        if (isset($options['left'])) {
            $this->left = $options['left'];
        } else {
            throw new ConditionOptionRequiredException('left');
        }

        if (isset($options['right'])) {
            $this->right = $options['right'];
        } else {
            throw new ConditionOptionRequiredException('right');
        }
    }
}
