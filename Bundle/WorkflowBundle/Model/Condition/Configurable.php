<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

use Oro\Bundle\WorkflowBundle\Model\Condition\ConditionAssembler;
use Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface;

class Configurable extends AbstractCondition
{
    const ALIAS = 'configurable';
    const DEFAULT_MESSAGE = 'Some conditions are not met';

    /**
     * @var array
     */
    protected $configuration;

    /**
     * @var ConditionInterface
     */
    protected $condition;

    /**
     * @var ConditionAssembler
     */
    protected $assembler;

    public function __construct(ConditionAssembler $assembler)
    {
        $this->assembler = $assembler;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        if ($this->message) {
            return $this->message;
        }

        return self::DEFAULT_MESSAGE;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $options)
    {
        $this->configuration = $options;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isAllowed($context)
    {
        if (!$this->condition) {
            $this->condition = $this->assembler->assemble($this->configuration);
        }

        return $this->condition->isAllowed($context);
    }
}
