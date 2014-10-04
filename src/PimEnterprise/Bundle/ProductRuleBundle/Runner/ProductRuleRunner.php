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
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Runner\DryRunnerInterface;

class ProductRuleRunner implements DryRunnerInterface
{
    public function run(RuleSubjectSetInterface $subjectSet)
    {
        echo sprintf("Running rule %s.\n", $subjectSet->getCode());

        foreach ($subjectSet->getSubjects() as $product) {
            echo sprintf("Applying rule %s on product %s.\n", $subjectSet->getCode(), $product->getIdentifier());
            $name = $product->getValue('name')->getData();
            $product->getValue('name')->setData($name . ' // ' . $product->getIdentifier());
        }
    }

    public function dryRun(RuleSubjectSetInterface $subjectSet)
    {
        echo sprintf("Dry running rule %s.\n", $subjectSet->getCode());

        $identifiers = array_map(
            function ($product) {
                return $product->getIdentifier();
            },
            $subjectSet->getSubjects()
        );

        echo sprintf(
            "%d products impacted by the rule %s: %s.",
            count($identifiers),
            $subjectSet->getCode(),
            implode(', ', $identifiers)
        );
    }

    public function supports(RuleSubjectSetInterface $subjectSet)
    {
        return 'product' === $subjectSet->getType();
    }
}
