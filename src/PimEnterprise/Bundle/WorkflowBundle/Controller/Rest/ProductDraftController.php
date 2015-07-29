<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Controller\Rest;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\WorkflowBundle\Manager\ProductDraftManager;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product draft rest controller
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class ProductDraftController
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var ProductDraftRepositoryInterface */
    protected $repository;

    /** @var ProductDraftManager */
    protected $manager;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /**
     * @param AuthorizationCheckerInterface   $authorizationChecker
     * @param ProductDraftRepositoryInterface $repository
     * @param ProductDraftManager             $manager
     * @param ProductRepositoryInterface      $productRepository
     * @param NormalizerInterface             $normalizer
     * @param TokenStorageInterface           $tokenStorage
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        ProductDraftRepositoryInterface $repository,
        ProductDraftManager $manager,
        ProductRepositoryInterface $productRepository,
        NormalizerInterface $normalizer,
        TokenStorageInterface $tokenStorage
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->repository           = $repository;
        $this->manager              = $manager;
        $this->productRepository    = $productRepository;
        $this->normalizer           = $normalizer;
        $this->tokenStorage         = $tokenStorage;
    }

    /**
     * Mark a product draft as ready
     *
     * @param int|string $id
     *
     * @throws AccessDeniedHttpException
     *
     * @return JsonResponse
     */
    public function readyAction($productId)
    {
        $product      = $this->findProductOr404($productId);
        $productDraft = $this->findDraftForProductOr404($product);

        if (!$this->authorizationChecker->isGranted(Attributes::OWN, $productDraft)) {
            throw new AccessDeniedHttpException();
        }

        $this->manager->markAsReady($productDraft);

        return new JsonResponse($this->normalizer->normalize($product, 'internal_api'));
    }

    /**
     * Find a product draft for a product
     *
     * @param ProductInterface $product
     *
     * @throws NotFoundHttpException
     *
     * @return ProductDraftInterface
     */
    protected function findDraftForProductOr404(ProductInterface $product)
    {
        $username     = $this->tokenStorage->getToken()->getUsername();
        $productDraft = $this->repository->findUserProductDraft($product, $username);
        if (null === $productDraft) {
            throw new NotFoundHttpException(sprintf('Draft for product %d not found', $product->getId()));
        }

        return $productDraft;
    }

    /**
     * Find a product by its id
     *
     * @param $productId
     *
     * @throws NotFoundHttpException
     *
     * @return ProductInterface
     */
    protected function findProductOr404($productId)
    {
        $product = $this->productRepository->findOneById($productId);
        if (null === $product) {
            throw new NotFoundHttpException(sprintf('Product with id %d not found', $productId));
        }

        return $product;
    }
}
