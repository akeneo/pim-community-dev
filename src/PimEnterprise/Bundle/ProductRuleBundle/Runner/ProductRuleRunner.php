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
use PimEnterprise\Bundle\ProductRuleBundle\Model\ProductRuleSubjectSetInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Runner\DryRunnerInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Selector\SelectorInterface;

class ProductRuleRunner implements DryRunnerInterface
{
    protected $selector;

    public function __construct(SelectorInterface $selector)
    {
        $this->selector = $selector;
    }

    public function run(RuleInterface $rule)
    {
        $subjectSet = $this->selector->select($rule);
        $products = $subjectSet->getSubjects();

        echo sprintf("Running rule %s.\n", $rule->getCode());

        foreach ($products as $product) {
            echo sprintf("Applying rule %s on product %s.\n", $rule->getCode(), $product->getIdentifier());
            $name = $product->getValue('name')->getData();
            $product->getValue('name')->setData($name . ' // ' . $product->getIdentifier());
        }
    }

    public function dryRun(RuleInterface $rule)
    {
        $subjectSet = $this->selector->select($rule);
        $products = $subjectSet->getSubjects();

        echo sprintf("Dry running rule %s.\n", $rule->getCode());

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

    public function supports(RuleInterface $rule)
    {
        return 'product' === $rule->getType();
    }
}
