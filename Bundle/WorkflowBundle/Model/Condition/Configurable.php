<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

use Doctrine\Common\Collections\Collection;

class Configurable extends AbstractCondition
{
    const ALIAS = 'configurable';

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
    public function initialize(array $options)
    {
        $this->configuration = $options;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isAllowed($context, Collection $errors = null)
    {
        if (!$this->condition) {
            $this->condition = $this->assembler->assemble($this->configuration);
        }

        return $this->condition->isAllowed($context, $errors);
    }
}
