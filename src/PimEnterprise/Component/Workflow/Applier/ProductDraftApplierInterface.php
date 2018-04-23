<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Workflow\Applier;

use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Workflow\Model\EntityWithValuesDraftInterface;

/**
 * Product draft applier interface
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
interface ProductDraftApplierInterface
{
    /**
     * Apply all changes on the product no matter the review statuses
     *
     * @param ProductInterface                     $product
     * @param EntityWithValuesDraftInterface $productDraft
     */
    public function applyAllChanges(ProductInterface $product, EntityWithValuesDraftInterface $productDraft);

    /**
     * Apply only changes with the status EntityWithValuesDraftInterface::TO_REVIEW on the product
     *
     * @param ProductInterface                     $product
     * @param EntityWithValuesDraftInterface $productDraft
     */
    public function applyToReviewChanges(ProductInterface $product, EntityWithValuesDraftInterface $productDraft);
}
