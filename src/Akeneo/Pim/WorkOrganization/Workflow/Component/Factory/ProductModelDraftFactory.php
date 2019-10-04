<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\DraftSource;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;

/**
 * Product model draft factory
 *
 * @author olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProductModelDraftFactory implements EntityWithValuesDraftFactory
{
    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /**
     * @param ProductModelRepositoryInterface $productModelRepository
     */
    public function __construct(ProductModelRepositoryInterface $productModelRepository)
    {
        $this->productModelRepository = $productModelRepository;
    }

    public function createEntityWithValueDraft(EntityWithValuesInterface $productModel, DraftSource $draftSource): EntityWithValuesDraftInterface
    {
        $fullProductModel = $this->productModelRepository->find($productModel->getId());

        $productModelDraft = new ProductModelDraft();
        $productModelDraft
            ->setEntityWithValue($fullProductModel)
            ->setAuthor($draftSource->getAuthor())
            ->setAuthorLabel($draftSource->getAuthorLabel())
            ->setSource($draftSource->getSource())
            ->setSourceLabel($draftSource->getSourceLabel())
            ->setCreatedAt(new \DateTime());

        return $productModelDraft;
    }
}
