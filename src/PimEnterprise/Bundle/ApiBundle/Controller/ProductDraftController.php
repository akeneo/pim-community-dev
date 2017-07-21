<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ApiBundle\Controller;

use Pim\Component\Api\Repository\ProductRepositoryInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\Exception\ResourceAccessDeniedHttpException;
use PimEnterprise\Component\Workflow\Applier\ProductDraftApplierInterface;
use PimEnterprise\Component\Workflow\Repository\ProductDraftRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Laurent Petard <laurent.petard@akeneo.com>
 */
class ProductDraftController
{
    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var ProductDraftRepositoryInterface */
    protected $productDraftRepository;

    /** @var ProductDraftApplierInterface */
    protected $productDraftApplier;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /**
     * @param ProductRepositoryInterface      $productRepository
     * @param ProductDraftRepositoryInterface $productDraftRepository
     * @param ProductDraftApplierInterface    $productDraftApplier
     * @param NormalizerInterface             $normalizer
     * @param TokenStorageInterface           $tokenStorage
     * @param AuthorizationCheckerInterface   $authorizationChecker
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductDraftRepositoryInterface $productDraftRepository,
        ProductDraftApplierInterface $productDraftApplier,
        NormalizerInterface $normalizer,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->productRepository = $productRepository;
        $this->productDraftRepository = $productDraftRepository;
        $this->productDraftApplier = $productDraftApplier;
        $this->normalizer = $normalizer;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param string $code
     *
     * @throws NotFoundHttpException             If the product does not exist
     *                                           Or if there is no draft created for the product and the current user
     * @throws ResourceAccessDeniedHttpException If the user has ownership on the product
     *                                           Or if user has only view permission on the product
     *
     * @return JsonResponse
     */
    public function getAction($code)
    {
        $product = $this->productRepository->findOneByIdentifier($code);

        if (null === $product) {
            throw new NotFoundHttpException(sprintf('Product "%s" does not exist.', $code));
        }

        $isOwner = $this->authorizationChecker->isGranted(Attributes::OWN, $product);

        if ($isOwner) {
            throw new ResourceAccessDeniedHttpException($product, sprintf(
                'You have ownership on the product "%s", you cannot create or retrieve a draft from this product.',
                $code
            ));
        }

        $canView = $this->authorizationChecker->isGranted(Attributes::VIEW, $product);
        $canEdit = $this->authorizationChecker->isGranted(Attributes::EDIT, $product);

        if ($canView && !$canEdit) {
            throw new ResourceAccessDeniedHttpException($product, sprintf(
                'You only have view permission on the product "%s", you cannot create or retrieve a draft from this product.',
                $code
            ));
        }

        $userToken = $this->tokenStorage->getToken();
        $productDraft = $this->productDraftRepository->findUserProductDraft($product, $userToken->getUsername());

        if (null === $productDraft) {
            throw new NotFoundHttpException(sprintf('There is no draft created for the product "%s".', $code));
        }

        $this->productDraftApplier->applyAllChanges($product, $productDraft);
        $normalizedProductDraft = $this->normalizer->normalize($product, 'external_api');

        return new JsonResponse($normalizedProductDraft);
    }
}
