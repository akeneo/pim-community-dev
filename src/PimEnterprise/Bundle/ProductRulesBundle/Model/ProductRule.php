<?php

namespace Pim\Bundle\ProductRulesBundle\Model;

use Pim\Bundle\RulesEngineBundle\Model\RuleInterface;

class ProductRule implements ProductRuleInterface, RuleInterface
{
    /** @var string */
    protected $expression;

    /** @var array */
    protected $context;

    /** @var ConditionInterface[] */
    protected $conditions;

    /** @var ActionInterface[] */
    protected $actions;

    public function getExpression()
    {
        return $this->expression;
    }

    public function setExpression($expression)
    {
        $this->expression = $expression;

        return $this;
    }
}
