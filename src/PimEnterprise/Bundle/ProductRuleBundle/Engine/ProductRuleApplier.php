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
use PimEnterprise\Bundle\RuleEngineBundle\Batch\BatchApplierInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;

/**
 * Applies product rules via a batch.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ProductRuleApplier implements BatchApplierInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /**
     * {@inheritdoc}
     */
    public function apply(RuleSubjectSetInterface $subjectSet)
    {
        /** @var ProductInterface[] $products */
        $products = $subjectSet->getSubjects();

        echo sprintf("Running rule %s.\n", $subjectSet->getCode());

        foreach ($products as $product) {
            echo sprintf("Applying rule %s on product %s.\n", $subjectSet->getCode(), $product->getIdentifier());
            $name = $product->getValue('name')->getData();
            $product->getValue('name')->setData($name . ' // ' . $product->getIdentifier());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(RuleSubjectSetInterface $subjectSet)
    {
        return 'product' === $subjectSet->getType();
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
