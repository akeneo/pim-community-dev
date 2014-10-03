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
use PimEnterprise\Bundle\RuleEngineBundle\Runner\DryRunnerInterface;

class ProductRuleRunner implements DryRunnerInterface
{
    public function run(RunnableRuleInterface $rule, $dryRun = false)
    {
        echo sprintf("Running rule %s.\n", $rule->getCode());
        $products = $this->getProducts($rule);

        foreach ($products as $product) {
            echo sprintf("Applying rule %s on product %s.\n", $rule->getCode(), $product->getIdentifier());
            $name = $product->getValue('name')->getData();
            $product->getValue('name')->setData($name . ' // ' . $product->getIdentifier());
        }
    }

    public function dryRun(RunnableRuleInterface $rule)
    {
        echo sprintf("Dry running rule %s.\n", $rule->getCode());
        $products = $this->getProducts($rule);

        $identifiers = array_map(
            function ($product) {
                return $product->getIdentifier();
            },
            $products
        );

        echo sprintf(
            "%d products impacted by the rule %s: %s.",
            count($identifiers),
            $rule->getCode(),
            implode(', ', $identifiers)
        );
    }

    public function supports(RunnableRuleInterface $rule)
    {
        return $rule instanceof ProductRunnableRuleInterface;
    }

    /**
     * @param RunnableRuleInterface $rule
     *
     * @return ProductInterface[]
     */
    private function getProducts(RunnableRuleInterface $rule)
    {
        return $rule->getQueryBuilder()->getQueryBuilder()->getQuery()->execute();
    }
}
