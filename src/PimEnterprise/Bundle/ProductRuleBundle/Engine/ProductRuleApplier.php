<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductRuleBundle\Engine;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Engine\ApplierInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;

/**
 * Applies product rules via a batch.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ProductRuleApplier implements ApplierInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply(RuleSubjectSetInterface $subjectSet)
    {
        /** @var ProductInterface[] $products */
        $products = $subjectSet->getSubjects();

        echo sprintf("Running rule %s on %s products.\n", $subjectSet->getCode(), count($products));

        $start = microtime(true);
        foreach ($products as $product) {
            $name = $product->getValue('name')->getData();
            $product->getValue('name')->setData($name . ' // ' . $product->getIdentifier());
        }

        echo sprintf("Done : %sms\n", round((microtime(true) - $start) * 100));
    }

    /**
     * {@inheritdoc}
     */
    public function supports(RuleSubjectSetInterface $subjectSet)
    {
        return 'product' === $subjectSet->getType();
    }
}
