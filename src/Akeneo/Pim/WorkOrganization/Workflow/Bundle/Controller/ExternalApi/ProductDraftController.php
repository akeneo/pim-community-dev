<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Controller\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Permission\Component\Exception\ResourceAccessDeniedException;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Applier\DraftApplierInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
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

    /** @var EntityWithValuesDraftRepositoryInterface */
    protected $productDraftRepository;

    /** @var DraftApplierInterface */
    protected $productDraftApplier;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /**
     * @param ProductRepositoryInterface               $productRepository
     * @param EntityWithValuesDraftRepositoryInterface $productDraftRepository
     * @param DraftApplierInterface                    $productDraftApplier
     * @param NormalizerInterface                      $normalizer
     * @param TokenStorageInterface                    $tokenStorage
     * @param AuthorizationCheckerInterface            $authorizationChecker
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        EntityWithValuesDraftRepositoryInterface $productDraftRepository,
        DraftApplierInterface $productDraftApplier,
        NormalizerInterface $normalizer,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        private SecurityFacadeInterface $security,
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
     * @throws NotFoundHttpException         If the product does not exist
     *                                       Or if there is no draft created for the product and the current user
     * @throws ResourceAccessDeniedException If the user has ownership on the product
     *                                       Or if user has only view permission on the product
     *
     * @return JsonResponse
     */
    public function getAction($code)
    {
        $this->denyAccessUnlessAclIsGranted();

        $product = $this->productRepository->findOneByIdentifier($code);

        if (null === $product) {
            throw new NotFoundHttpException(sprintf('Product "%s" does not exist.', $code));
        }

        $isOwner = $this->authorizationChecker->isGranted(Attributes::OWN, $product);

        if ($isOwner) {
            throw new ResourceAccessDeniedException($product, sprintf(
                'You have ownership on the product "%s", you cannot create or retrieve a draft from this product.',
                $code
            ));
        }

        $canView = $this->authorizationChecker->isGranted(Attributes::VIEW, $product);
        $canEdit = $this->authorizationChecker->isGranted(Attributes::EDIT, $product);

        if ($canView && !$canEdit) {
            throw new ResourceAccessDeniedException($product, sprintf(
                'You only have view permission on the product "%s", you cannot create or retrieve a draft from this product.',
                $code
            ));
        }

        $userToken = $this->tokenStorage->getToken();
        $productDraft = $this->productDraftRepository->findUserEntityWithValuesDraft($product, $userToken->getUserIdentifier());

        if (null === $productDraft) {
            throw new NotFoundHttpException(sprintf('There is no draft created for the product "%s".', $code));
        }

        $this->productDraftApplier->applyAllChanges($product, $productDraft);
        $normalizedProductDraft = $this->normalizer->normalize($product, 'external_api');

        return new JsonResponse($normalizedProductDraft);
    }

    private function denyAccessUnlessAclIsGranted(): void
    {
        if (!$this->security->isGranted('pim_api_product_list')) {
            throw new AccessDeniedHttpException('Access forbidden. You are not allowed to list products.');
        }
    }
}
