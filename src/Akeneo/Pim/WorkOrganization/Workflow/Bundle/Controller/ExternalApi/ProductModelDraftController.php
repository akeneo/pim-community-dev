<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Controller\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Permission\Component\Exception\ResourceAccessDeniedException;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Applier\DraftApplierInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductModelDraftController
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $productModelRepository;

    /** @var EntityWithValuesDraftRepositoryInterface */
    protected $productModelDraftRepository;

    /** @var DraftApplierInterface */
    protected $draftApplier;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    public function __construct(
        IdentifiableObjectRepositoryInterface $productModelRepository,
        EntityWithValuesDraftRepositoryInterface $productModelDraftRepository,
        DraftApplierInterface $draftApplier,
        NormalizerInterface $normalizer,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        private SecurityFacadeInterface $security,
    ) {
        $this->productModelRepository = $productModelRepository;
        $this->productModelDraftRepository = $productModelDraftRepository;
        $this->draftApplier = $draftApplier;
        $this->normalizer = $normalizer;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @throws NotFoundHttpException         If the product model does not exist
     *                                       Or if there is no draft created for the product model and the current user
     * @throws ResourceAccessDeniedException If the user has ownership on the product mode
     *                                       Or if user has only view permission on the product model
     */
    public function getAction(string $code): JsonResponse
    {
        $this->denyAccessUnlessAclIsGranted();

        $productModel = $this->productModelRepository->findOneByIdentifier($code);
        if (null === $productModel) {
            throw new NotFoundHttpException(sprintf('Product model "%s" does not exist.', $code));
        }

        $this->userHasOwnPermissions($productModel, $code);
        $this->userHasViewPermissions($productModel, $code);

        $userToken = $this->tokenStorage->getToken();
        $productModelDraft = $this->productModelDraftRepository->findUserEntityWithValuesDraft($productModel, $userToken->getUserIdentifier());

        if (null === $productModelDraft) {
            throw new NotFoundHttpException(sprintf('There is no draft created for the product model "%s".', $code));
        }

        $this->draftApplier->applyAllChanges($productModel, $productModelDraft);
        $normalizedProductModelDraft = $this->normalizer->normalize($productModel, 'external_api');

        return new JsonResponse($normalizedProductModelDraft);
    }

    private function userHasOwnPermissions(ProductModelInterface $productModel, string $code): void
    {
        $isOwner = $this->authorizationChecker->isGranted(Attributes::OWN, $productModel);

        if ($isOwner) {
            throw new ResourceAccessDeniedException($productModel, sprintf(
                'You have ownership on the product model "%s", you cannot create or retrieve a draft from this product model.',
                $code
            ));
        }
    }

    private function userHasViewPermissions(ProductModelInterface $productModel, string $code): void
    {
        $canView = $this->authorizationChecker->isGranted(Attributes::VIEW, $productModel);
        $canEdit = $this->authorizationChecker->isGranted(Attributes::EDIT, $productModel);

        if ($canView && !$canEdit) {
            throw new ResourceAccessDeniedException($productModel, sprintf(
                'You only have view permission on the product model "%s", you cannot create or retrieve a draft from this product model.',
                $code
            ));
        }
    }

    private function denyAccessUnlessAclIsGranted(): void
    {
        if (!$this->security->isGranted('pim_api_product_list')) {
            throw new AccessDeniedHttpException('Access forbidden. You are not allowed to list products.');
        }
    }
}
