<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Workflow\Factory;

use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use PimEnterprise\Component\Workflow\Model\EntityWithValuesDraftInterface;
use PimEnterprise\Component\Workflow\Model\ProductModelDraft;

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

    /**
     * {@inheritdoc}
     */
    public function createEntityWithValueDraft(EntityWithValuesInterface $productModel, string $username): EntityWithValuesDraftInterface
    {
        $fullProductModel = $this->productModelRepository->find($productModel->getId());

        $productModelDraft = new ProductModelDraft();
        $productModelDraft
            ->setEntityWithValue($fullProductModel)
            ->setAuthor($username)
            ->setCreatedAt(new \DateTime());

        return $productModelDraft;
    }
}
