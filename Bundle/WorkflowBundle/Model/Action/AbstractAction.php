<?php

namespace Oro\Bundle\WorkflowBundle\Model\Action;

use Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface;
use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;

abstract class AbstractAction implements ActionInterface
{
    /**
     * @var ContextAccessor
     */
    protected $contextAccessor;

    /**
     * @var ConditionInterface
     */
    protected $condition;

    /**
     * @param ContextAccessor $contextAccessor
     */
    public function __construct(ContextAccessor $contextAccessor)
    {
        $this->contextAccessor = $contextAccessor;
    }

    /**
     * {@inheritDoc}
     */
    public function setCondition(ConditionInterface $condition)
    {
        $this->condition = $condition;
    }

    /**
     * @param mixed $context
     */
    public function execute($context)
    {
        if ($this->isAllowed($context)) {
            $this->executeAction($context);
        }
    }

    /**
     * @param mixed $context
     * @return bool
     */
    protected function isAllowed($context)
    {
        if (!$this->condition) {
            return true;
        }

        return $this->condition->isAllowed($context);
    }

    /**
     * @param array $options
     * @param string $key
     * @param mixed|null $default
     * @return null
     */
    protected function getOption(array $options, $key, $default = null)
    {
        return array_key_exists($key, $options) ? $options[$key] : $default;
    }

    /**
     * @param mixed $context
     */
    abstract protected function executeAction($context);
}
