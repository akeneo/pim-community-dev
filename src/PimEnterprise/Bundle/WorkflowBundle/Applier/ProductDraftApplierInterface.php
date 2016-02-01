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

use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;

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
     * @param ProductInterface      $product
     * @param ProductDraftInterface $productDraft
     */
    public function applyAllChanges(ProductInterface $product, ProductDraftInterface $productDraft);

    /**
     * Apply only changes with the status ProductDraftInterface::TO_REVIEW on the product
     *
     * @param ProductInterface      $product
     * @param ProductDraftInterface $productDraft
     */
    public function applyToReviewChanges(ProductInterface $product, ProductDraftInterface $productDraft);
}
