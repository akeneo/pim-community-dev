<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductRuleBundle\Runner;

use PimEnterprise\Bundle\ProductRuleBundle\Model\ProductRunnableRuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RunnableRuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Runner\RunnerInterface;

class ProductRuleRunner implements RunnerInterface
{
    public function run(RunnableRuleInterface $rule)
    {
        $queryBuilder = $rule->getQueryBuilder();
        $products = $queryBuilder->getQuery()->execute();

        // TODO execute actions
    }

    public function supports(RunnableRuleInterface $rule)
    {
        return $rule instanceof ProductRunnableRuleInterface;
    }
}
