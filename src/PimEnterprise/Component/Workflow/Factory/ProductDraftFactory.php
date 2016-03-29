<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Workflow\Factory;

use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Workflow\Model\ProductDraft;
use PimEnterprise\Component\Workflow\Model\ProductDraftInterface;

/**
 * Product product draft factory
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class ProductDraftFactory
{
    /**
     * Create and configure a ProductDraft instance
     *
     * @param ProductInterface $product
     * @param string           $username
     *
     * @return ProductDraftInterface
     */
    public function createProductDraft(ProductInterface $product, $username)
    {
        $productDraft = new ProductDraft();
        $productDraft
            ->setProduct($product)
            ->setAuthor($username)
            ->setCreatedAt(new \DateTime());

        return $productDraft;
    }
}
