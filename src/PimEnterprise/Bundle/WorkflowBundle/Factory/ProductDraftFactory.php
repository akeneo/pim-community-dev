<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Factory;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;

/**
 * Product product draft factory
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductDraftFactory
{
    /**
     * Create and configure a ProductDraft instance
     *
     * @param ProductInterface $product
     * @param string           $username
     *
     * @return ProductDraft
     */
    public function createProposition(ProductInterface $product, $username)
    {
        $productDraft = new ProductDraft();
        $productDraft
            ->setProduct($product)
            ->setAuthor($username)
            ->setCreatedAt(new \DateTime());

        return $productDraft;
    }
}
