<?php

namespace Pim\Bundle\ProductRulesBundle\Model;

use Pim\Bundle\RulesEngineBundle\Model\RuleInterface;

interface ProductRuleInterface extends RuleInterface
{
    public function getExpression();
    public function setExpression($expression);
}
