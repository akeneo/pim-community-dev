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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Exception\ValidatorException;

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
     * @param Request    $request
     * @param int|string $productId
     *
     * @throws AccessDeniedHttpException
     *
     * @return JsonResponse
     */
    public function readyAction(Request $request, $productId)
    {
        $product      = $this->findProductOr404($productId);
        $productDraft = $this->findDraftForProductOr404($product);
        $comment      = ("" === $request->get('comment')) ? null : $request->get('comment');

        if (!$this->authorizationChecker->isGranted(Attributes::OWN, $productDraft)) {
            throw new AccessDeniedHttpException();
        }

        $this->manager->markAsReady($productDraft, $comment);

        return new JsonResponse($this->normalizer->normalize($product, 'internal_api'));
    }

    /**
     * Approve a product draft
     *
     * @param mixed $id
     *
     * @throws \LogicException
     * @throws AccessDeniedHttpException
     *
     * @return JsonResponse
     */
    public function approveAction($id)
    {
        $productDraft = $this->findProductDraftOr404($id);

        if (ProductDraftInterface::READY !== $productDraft->getStatus()) {
            throw new \LogicException('A product draft that is not ready can not be approved');
        }

        if (!$this->authorizationChecker->isGranted(Attributes::OWN, $productDraft->getProduct())) {
            throw new AccessDeniedHttpException();
        }

        if (!$this->authorizationChecker->isGranted(Attributes::EDIT_ATTRIBUTES, $productDraft)) {
            throw new AccessDeniedHttpException();
        }

        try {
            $this->manager->approve($productDraft);
        } catch (ValidatorException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 400);
        }

        return new JsonResponse($this->normalizer->normalize($productDraft->getProduct(), 'internal_api'));
    }

    /**
     * Reject a product draft
     *
     * @param mixed $id
     *
     * @throws \LogicException
     * @throws AccessDeniedHttpException
     *
     * @return JsonResponse
     */
    public function rejectAction($id)
    {
        $productDraft = $this->findProductDraftOr404($id);

        if (!$this->authorizationChecker->isGranted(Attributes::OWN, $productDraft->getProduct())) {
            throw new AccessDeniedHttpException();
        }

        if (!$this->authorizationChecker->isGranted(Attributes::EDIT_ATTRIBUTES, $productDraft)) {
            throw new AccessDeniedHttpException();
        }

        $this->manager->refuse($productDraft);

        return new JsonResponse($this->normalizer->normalize($productDraft->getProduct(), 'internal_api'));
    }

    /**
     * Find a product draft for the current user and specified product
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
            throw new NotFoundHttpException(sprintf('Draft for product %s not found', $product->getId()));
        }

        return $productDraft;
    }

    /**
     * Find a product by its id
     *
     * @param mixed $productId
     *
     * @throws NotFoundHttpException
     *
     * @return ProductInterface
     */
    protected function findProductOr404($productId)
    {
        $product = $this->productRepository->findOneById($productId);
        if (null === $product) {
            throw new NotFoundHttpException(sprintf('Product with id %s not found', $productId));
        }

        return $product;
    }

    /**
     * Find a product draft by its id
     *
     * @param mixed $draftId
     *
     * @throws NotFoundHttpException
     *
     * @return ProductDraftInterface
     */
    protected function findProductDraftOr404($draftId)
    {
        $productDraft = $this->repository->find($draftId);
        if (null === $productDraft) {
            throw new NotFoundHttpException(sprintf('Draft with id %s not found', $draftId));
        }

        return $productDraft;
    }
}
