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

use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes as SecurityAttributes;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Bundle\WorkflowBundle\Manager\ProductDraftManager;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Security\Attributes as WorkflowAttributes;
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

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var UserContext */
    protected $userContext;

    /** @var array */
    protected $supportedReviewActions = ['approve', 'refuse'];

    /**
     * @param AuthorizationCheckerInterface   $authorizationChecker
     * @param ProductDraftRepositoryInterface $repository
     * @param ProductDraftManager             $manager
     * @param ProductRepositoryInterface      $productRepository
     * @param NormalizerInterface             $normalizer
     * @param TokenStorageInterface           $tokenStorage
     * @param AttributeRepositoryInterface    $attributeRepository
     * @param ChannelRepositoryInterface      $channelRepository
     * @param LocaleRepositoryInterface       $localeRepository
     * @param UserContext                     $userContext
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        ProductDraftRepositoryInterface $repository,
        ProductDraftManager $manager,
        ProductRepositoryInterface $productRepository,
        NormalizerInterface $normalizer,
        TokenStorageInterface $tokenStorage,
        AttributeRepositoryInterface $attributeRepository,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        UserContext $userContext
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->repository           = $repository;
        $this->manager              = $manager;
        $this->productRepository    = $productRepository;
        $this->normalizer           = $normalizer;
        $this->tokenStorage         = $tokenStorage;
        $this->attributeRepository  = $attributeRepository;
        $this->channelRepository    = $channelRepository;
        $this->localeRepository     = $localeRepository;
        $this->userContext          = $userContext;
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
        $comment      = $request->get('comment') ?: null;

        if (!$this->authorizationChecker->isGranted(SecurityAttributes::OWN, $productDraft)) {
            throw new AccessDeniedHttpException();
        }

        $this->manager->markAsReady($productDraft, $comment);

        $normalizationContext = $this->userContext->toArray() + [
            'filter_type'                => 'pim.internal_api.product_value.view',
            'disable_grouping_separator' => true
        ];

        return new JsonResponse($this->normalizer->normalize(
            $product,
            'internal_api',
            $normalizationContext
        ));
    }

    /**
     * Approve an attribute change in a product draft
     *
     * @param Request $request
     * @param mixed   $id
     * @param string  $code
     * @param string  $action either "approve" or "refuse"
     *
     * @throws NotFoundHttpException
     * @throws \LogicException
     * @throws AccessDeniedHttpException
     *
     * @return JsonResponse
     */
    public function partialReviewAction(Request $request, $id, $code, $action)
    {
        $productDraft = $this->findProductDraftOr404($id);

        if (!in_array($action, $this->supportedReviewActions)) {
            throw new \LogicException(sprintf('"%s" is not a valid review action', $action));
        }

        if (!$this->authorizationChecker->isGranted(SecurityAttributes::OWN, $productDraft->getProduct())) {
            throw new AccessDeniedHttpException();
        }

        $attribute = $this->findAttributeOr404($code);
        if (!$this->authorizationChecker->isGranted(SecurityAttributes::EDIT_ATTRIBUTES, $attribute->getGroup())) {
            throw new AccessDeniedHttpException();
        }

        $channel = null;
        if ($request->query->has('scope')) {
            $channel = $this->findChannelOr404($request->query->get('scope'));
        }

        $locale = null;
        if ($request->query->has('locale')) {
            $locale = $this->findLocaleOr404($request->query->get('locale'));
            if (!$this->authorizationChecker->isGranted(SecurityAttributes::EDIT_ITEMS, $locale)) {
                throw new AccessDeniedHttpException();
            }
        }

        try {
            $method = 'partial' . ucfirst($action);
            $this->manager->$method($productDraft, $attribute, $channel, $locale, [
                'comment' => $request->query->get('comment')
            ]);
        } catch (ValidatorException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 400);
        }

        $normalizationContext = $this->userContext->toArray() + [
            'filter_type'                => 'pim.internal_api.product_value.view',
            'disable_grouping_separator' => true
        ];

        return new JsonResponse($this->normalizer->normalize(
            $productDraft->getProduct(),
            'internal_api',
            $normalizationContext
        ));
    }

    /**
     * Approve a product draft
     *
     * @param Request $request
     * @param mixed   $id
     *
     * @throws \LogicException
     * @throws AccessDeniedHttpException
     *
     * @return JsonResponse
     */
    public function approveAction(Request $request, $id)
    {
        $productDraft = $this->findProductDraftOr404($id);

        if (ProductDraftInterface::READY !== $productDraft->getStatus()) {
            throw new \LogicException('A product draft that is not ready can not be approved');
        }

        if (!$this->authorizationChecker->isGranted(SecurityAttributes::OWN, $productDraft->getProduct())) {
            throw new AccessDeniedHttpException();
        }

        if (!$this->authorizationChecker->isGranted(WorkflowAttributes::FULL_REVIEW, $productDraft)) {
            throw new AccessDeniedHttpException();
        }

        try {
            $this->manager->approve($productDraft, [
                'comment' => $request->request->get('comment')
            ]);
        } catch (ValidatorException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 400);
        }

        $normalizationContext = $this->userContext->toArray() + [
            'filter_type'                => 'pim.internal_api.product_value.view',
            'disable_grouping_separator' => true
        ];

        return new JsonResponse($this->normalizer->normalize(
            $productDraft->getProduct(),
            'internal_api',
            $normalizationContext
        ));
    }

    /**
     * Reject a product draft
     *
     * @param Request $request
     * @param mixed   $id
     *
     * @throws \LogicException
     * @throws AccessDeniedHttpException
     *
     * @return JsonResponse
     */
    public function rejectAction(Request $request, $id)
    {
        $productDraft = $this->findProductDraftOr404($id);

        if (!$this->authorizationChecker->isGranted(SecurityAttributes::OWN, $productDraft->getProduct())) {
            throw new AccessDeniedHttpException();
        }

        if (!$this->authorizationChecker->isGranted(WorkflowAttributes::FULL_REVIEW, $productDraft)) {
            throw new AccessDeniedHttpException();
        }

        $this->manager->refuse($productDraft, [
            'comment' => $request->request->get('comment')
        ]);

        $normalizationContext = $this->userContext->toArray() + [
            'filter_type'                => 'pim.internal_api.product_value.view',
            'disable_grouping_separator' => true
        ];

        return new JsonResponse($this->normalizer->normalize(
            $productDraft->getProduct(),
            'internal_api',
            $normalizationContext
        ));
    }

    /**
     * Remove a product draft
     *
     * @param Request $request
     * @param mixed   $id
     *
     * @throws \LogicException
     * @throws AccessDeniedHttpException
     *
     * @return JsonResponse
     */
    public function removeAction(Request $request, $id)
    {
        $productDraft = $this->findProductDraftOr404($id);

        if (!$this->authorizationChecker->isGranted(SecurityAttributes::OWN, $productDraft->getProduct())) {
            throw new AccessDeniedHttpException();
        }

        if (!$this->authorizationChecker->isGranted(WorkflowAttributes::FULL_REVIEW, $productDraft)) {
            throw new AccessDeniedHttpException();
        }

        $this->manager->remove($productDraft, [
            'comment' => $request->request->get('comment')
        ]);

        $normalizationContext = $this->userContext->toArray() + [
            'filter_type'                => 'pim.internal_api.product_value.view',
            'disable_grouping_separator' => true
        ];

        return new JsonResponse($this->normalizer->normalize(
            $productDraft->getProduct(),
            'internal_api',
            $normalizationContext
        ));
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

    /**
     * @param string $code
     *
     * @throws NotFoundHttpException
     *
     * @return AttributeInterface
     */
    protected function findAttributeOr404($code)
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($code);
        if (null === $attribute) {
            throw new NotFoundHttpException(sprintf('Attribute "%s" not found', $code));
        }

        return $attribute;
    }

    /**
     * @param string $code
     *
     * @throws NotFoundHttpException
     *
     * @return ChannelInterface
     */
    protected function findChannelOr404($code)
    {
        $channel = $this->channelRepository->findOneByIdentifier($code);
        if (null === $channel) {
            throw new NotFoundHttpException(sprintf('Channel "%s" not found', $code));
        }

        return $channel;
    }

    /**
     * @param string $code
     *
     * @throws NotFoundHttpException
     *
     * @return LocaleInterface
     */
    protected function findLocaleOr404($code)
    {
        $locale = $this->localeRepository->findOneByIdentifier($code);
        if (null === $locale) {
            throw new NotFoundHttpException(sprintf('Locale "%s" not found', $code));
        }

        return $locale;
    }
}
