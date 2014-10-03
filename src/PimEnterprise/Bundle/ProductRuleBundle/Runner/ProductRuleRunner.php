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

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\ProductRuleBundle\Model\ProductRunnableRuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RunnableRuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Runner\RunnerInterface;

class ProductRuleRunner implements RunnerInterface
{
    public function run(RunnableRuleInterface $rule, $dryRun = false)
    {
        /** @var ProductInterface[] $products */
        $products = $rule->getQueryBuilder()->getQueryBuilder()->getQuery()->execute();

        foreach ($products as $product) {
            echo sprintf("Applying rule %s on product %s.\n", $rule->getCode(), $product->getIdentifier());
            $name = $product->getValue('name')->getData();
            $product->getValue('name')->setData($name . ' // ' . $product->getIdentifier());
        }
    }

    public function supports(RunnableRuleInterface $rule)
    {
        return $rule instanceof ProductRunnableRuleInterface;
    }
}
