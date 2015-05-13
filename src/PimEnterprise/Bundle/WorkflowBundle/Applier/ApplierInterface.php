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
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;

/**
 * Applier interface
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
interface ApplierInterface
{
    /**
     * Apply a product
     *
     * @param ProductInterface $product
     * @param ProductDraft     $productDraft
     */
    public function apply(ProductInterface $product, ProductDraft $productDraft);
}
