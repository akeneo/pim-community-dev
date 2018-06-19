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

use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use PimEnterprise\Component\Workflow\Model\EntityWithValuesDraftInterface;
use PimEnterprise\Component\Workflow\Model\ProductDraft;

/**
 * Product product draft factory
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class ProductDraftFactory implements EntityWithValuesDraftFactory
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /**
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Create and configure a ProductDraft instance
     *
     * @param ProductInterface $product
     * @param string           $username
     *
     * @return EntityWithValuesDraftInterface
     */
    public function createEntityWithValueDraft(EntityWithValuesInterface $product, string $username): EntityWithValuesDraftInterface
    {
        $fullProduct = $this->productRepository->find($product->getId());

        $productDraft = new ProductDraft();
        $productDraft
            ->setEntityWithValue($fullProduct)
            ->setAuthor($username)
            ->setCreatedAt(new \DateTime());

        return $productDraft;
    }
}
