<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Controller\Rest;

use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Controller that handle request based on product in the workflow context
 *
 * @author Clement Gautier <clement.gautier@akeneo.com>
 */
class ProductController
{
    /** @var EntityWithValuesDraftRepositoryInterface */
    protected $repository;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ObjectFilterInterface */
    protected $objectFilter;

    /**
     * @param EntityWithValuesDraftRepositoryInterface $repository
     * @param ProductRepositoryInterface      $productRepository
     * @param NormalizerInterface             $normalizer
     * @param ObjectFilterInterface           $objectFilter
     */
    public function __construct(
        EntityWithValuesDraftRepositoryInterface $repository,
        ProductRepositoryInterface $productRepository,
        NormalizerInterface $normalizer,
        ObjectFilterInterface $objectFilter
    ) {
        $this->repository = $repository;
        $this->productRepository = $productRepository;
        $this->normalizer = $normalizer;
        $this->objectFilter = $objectFilter;
    }

    /**
     * Return all drafts of the given product excluding the current user's one.
     *
     * @param string $productId
     *
     * @throws NotFoundHttpException
     *
     * @return JsonResponse
     */
    public function indexAction($productId)
    {
        $product = $this->productRepository->find($productId);

        if (null === $product) {
            throw new NotFoundHttpException(sprintf('Product with id %s not found', $productId));
        }

        if ($this->objectFilter->filterObject($product, 'pim.internal_api.product.view')) {
            throw new NotFoundHttpException(sprintf('Product with id %s not found', $productId));
        }

        return new JsonResponse($this->normalizer->normalize(
            $this->repository->findByEntityWithValues($product),
            'internal_api'
        ));
    }
}
