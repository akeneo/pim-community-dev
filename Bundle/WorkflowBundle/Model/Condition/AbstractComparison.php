<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;
use Oro\Bundle\WorkflowBundle\Exception\ConditionOptionRequiredException;

abstract class AbstractComparison implements ConditionInterface
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
     * @var ContextAccessor
     */
    protected $contextAccessor;

    /**
     * Constructor
     *
     * @param ContextAccessor $contextAccessor
     */
    public function __construct(ContextAccessor $contextAccessor)
    {
        $this->contextAccessor = $contextAccessor;
    }

    /**
     * Check if values equals.
     *
     * @param mixed $context
     * @return boolean
     */
    public function isAllowed($context)
    {
        return $this->doCompare(
            $this->contextAccessor->getValue($context, $this->left),
            $this->contextAccessor->getValue($context, $this->right)
        );
    }

    /**
     * Compare two values according to logic of condition
     *
     * @param mixed $left
     * @param mixed $right
     * @return boolean
     */
    abstract protected function doCompare($left, $right);

    /**
     * Initialize condition options
     *
     * @param array $options
     * @return AbstractComparison
     * @throws ConditionOptionRequiredException If "left" or "right" option is empty
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

        return $this;
    }
}
