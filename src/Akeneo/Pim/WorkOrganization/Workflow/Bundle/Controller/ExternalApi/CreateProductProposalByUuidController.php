<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Controller\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Permission\Component\Authorization\FetchUserRightsOnProductInterface;
use Akeneo\Pim\Permission\Component\Exception\ResourceAccessDeniedException;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Manager\EntityWithValuesDraftManager;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Webmozart\Assert\Assert;

final class CreateProductProposalByUuidController
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private EntityWithValuesDraftRepositoryInterface $productDraftRepository,
        private FetchUserRightsOnProductInterface $fetchUserRights,
        private EntityWithValuesDraftManager $productDraftManager,
        private TokenStorageInterface $tokenStorage,
        private SecurityFacadeInterface $security
    ) {
    }

    public function __invoke(Request $request, string $uuid): Response
    {
        if (!$this->security->isGranted('pim_api_product_edit')) {
            throw new AccessDeniedHttpException('Access forbidden. You are not allowed to create or update products.');
        }

        $uuid = Uuid::fromString($uuid)->toString();

        try {
            $product = $this->productRepository->find($uuid);
        } catch (ResourceAccessDeniedException) {
            throw new NotFoundHttpException(\sprintf('Product "%s" does not exist or you do not have permission to access it.', $uuid));
        }

        if (null === $product) {
            throw new NotFoundHttpException(sprintf('Product "%s" does not exist.', $uuid));
        }

        $user = $this->tokenStorage->getToken()?->getUser();
        Assert::isInstanceOf($user, UserInterface::class);
        $userRights = $this->fetchUserRights->fetchByUuid(Uuid::fromString($uuid), $user->getId());

        if ($userRights->isProductEditable()) {
            throw new ResourceAccessDeniedException(
                $product,
                \sprintf('You have ownership on the product "%s", you cannot send a draft for approval.', $uuid)
            );
        } elseif (!$userRights->canApplyDraftOnProduct()) {
            throw new ResourceAccessDeniedException(
                $product,
                \sprintf('You only have view permission on the product "%s", you cannot send a draft for approval.', $uuid)
            );
        }

        $productDraft = $this->productDraftRepository->findUserEntityWithValuesDraft($product, $user->getUserIdentifier());
        if (null === $productDraft) {
            throw new UnprocessableEntityHttpException('You should create a draft before submitting it for approval.');
        }

        Assert::isInstanceOf($productDraft, EntityWithValuesDraftInterface::class);
        if (ProductDraft::READY === $productDraft->getStatus()) {
            throw new UnprocessableEntityHttpException('You already submitted your draft for approval.');
        }
        $this->productDraftManager->markAsReady($productDraft);

        return new Response(null, Response::HTTP_CREATED);
    }
}
