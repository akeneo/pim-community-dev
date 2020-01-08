<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Controller\Rest;

use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use PimEnterprise\Component\Workflow\Repository\EntityWithValuesDraftRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Controller that handle request based on product model in the workflow context
 *
 * @author Quentin Favrie <quentin.favrie@akeneo.com>
 */
class ProductModelController
{
    /** @var EntityWithValuesDraftRepositoryInterface */
    protected $repository;

    /** @var ProductModelRepositoryInterface */
    protected $productModelRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ObjectFilterInterface */
    protected $objectFilter;

    /**
     * @param EntityWithValuesDraftRepositoryInterface $repository
     * @param ProductModelRepositoryInterface $productModelRepository
     * @param NormalizerInterface $normalizer
     * @param ObjectFilterInterface $objectFilter
     */
    public function __construct(
        EntityWithValuesDraftRepositoryInterface $repository,
        ProductModelRepositoryInterface $productModelRepository,
        NormalizerInterface $normalizer,
        ObjectFilterInterface $objectFilter
    ) {
        $this->repository = $repository;
        $this->productModelRepository = $productModelRepository;
        $this->normalizer = $normalizer;
        $this->objectFilter = $objectFilter;
    }

    /**
     * Return all drafts of the given product model excluding the current user's one.
     *
     * @param string $productModelId
     *
     * @return JsonResponse
     * @throws NotFoundHttpException
     */
    public function indexAction($productModelId)
    {
        $productModel = $this->productModelRepository->find($productModelId);

        if (null === $productModel) {
            throw new NotFoundHttpException(sprintf('Product model with id %s not found', $productModelId));
        }

        if ($this->objectFilter->filterObject($productModel, 'pim.internal_api.product.view')) {
            throw new NotFoundHttpException(sprintf('Product model with id %s not found', $productModelId));
        }

        return new JsonResponse($this->normalizer->normalize(
            $this->repository->findByEntityWithValues($productModel),
            'internal_api'
        ));
    }
}
