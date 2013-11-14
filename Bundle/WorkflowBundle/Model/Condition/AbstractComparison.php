<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

use Oro\Bundle\WorkflowBundle\Exception\ConditionException;
use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;

abstract class AbstractComparison extends AbstractCondition
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
     * {@inheritdoc}
     */
    protected function getMessageParameters($context)
    {
        return array(
            '{{ left }}' => $this->contextAccessor->getValue($context, $this->left),
            '{{ right }}' => $this->contextAccessor->getValue($context, $this->right)
        );
    }

    /**
     * Check if values equals.
     *
     * @param mixed $context
     * @return boolean
     */
    protected function isConditionAllowed($context)
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
    // @codeCoverageIgnoreStart
    abstract protected function doCompare($left, $right);
    // @codeCoverageIgnoreEnd

    /**
     * Initialize condition options
     *
     * @param array $options
     * @return AbstractComparison
     * @throws ConditionException If options contains not two values
     * @throws ConditionException If "left" or "right" option is empty
     */
    public function initialize(array $options)
    {
        if (2 !== count($options)) {
            throw new ConditionException(
                sprintf(
                    'Options must have 2 elements, but %d given',
                    count($options)
                )
            );
        }

        if (isset($options['left'])) {
            $this->left = $options['left'];
        } elseif (isset($options[0])) {
            $this->left = $options[0];
        } else {
            throw new ConditionException('Option "left" is required.');
        }

        if (isset($options['right'])) {
            $this->right = $options['right'];
        } elseif (isset($options[1])) {
            $this->right = $options[1];
        } else {
            throw new ConditionException('Option "right" is required.');
        }

        return $this;
    }
}
