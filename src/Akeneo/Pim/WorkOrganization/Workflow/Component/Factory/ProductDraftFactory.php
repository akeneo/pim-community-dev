<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\DraftSource;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;

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

    public function createEntityWithValueDraft(EntityWithValuesInterface $product, DraftSource $draftSource): EntityWithValuesDraftInterface
    {
        $fullProduct = $this->productRepository->find($product->getId());

        $productDraft = new ProductDraft();
        $productDraft
            ->setEntityWithValue($fullProduct)
            ->setAuthor($draftSource->getAuthor())
            ->setAuthorLabel($draftSource->getAuthorLabel())
            ->setSource($draftSource->getSource())
            ->setSourceLabel($draftSource->getSourceLabel())
            ->setCreatedAt(new \DateTime());

        return $productDraft;
    }
}
