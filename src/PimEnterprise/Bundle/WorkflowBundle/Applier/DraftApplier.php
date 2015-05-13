<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Applier;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdater;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;

/**
 * Apply a draft
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class DraftApplier implements ApplierInterface
{
    /** @var ProductUpdater */
    protected $productUpdater;

    /**
     * @param ProductUpdater $productUpdater
     */
    public function __construct(ProductUpdater $productUpdater)
    {
        $this->productUpdater = $productUpdater;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(ProductInterface $product, ProductDraft $productDraft)
    {
        $changes = $productDraft->getChanges();

        if (!isset($changes['values'])) {
            return null;
        }

        foreach ($changes['values'] as $code => $values) {
            foreach ($values as $value) {
                $this->productUpdater->setData(
                    $product,
                    $code,
                    $value['value'],
                    ['locale' => $value['locale'], 'scope' => $value['scope']]
                );
            }
        }
    }
}
