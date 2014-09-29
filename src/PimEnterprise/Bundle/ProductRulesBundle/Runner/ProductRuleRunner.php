<?php

namespace Pim\Bundle\ProductRulesBundle\Runner;

use Pim\Bundle\RulesEngineBundle\Runner\RunnerInterface;
use Pim\Bundle\ProductRulesBundle\Model\ProductRuleInterface;

class ProductRuleRunner implements RunnerInterface
{
    public function run(RuleInterface $rule)
    {
        $queryBuilder = $rule->getQueryBuilder();
        $products = $queryBuilder->getQuery()->execute();

        // TODO execute actions
    }

    public function supports(RuleInterface $rule)
    {
        return $rule instanceof ProductRuleInterface;
    }
}
