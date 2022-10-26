<?php

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
use Akeneo\Pim\WorkOrganization\Workflow\Component\Applier\DraftApplierInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

final class GetProductDraftWithUuidController
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private EntityWithValuesDraftRepositoryInterface $productDraftRepository,
        private DraftApplierInterface $productDraftApplier,
        private NormalizerInterface $normalizer,
        private FetchUserRightsOnProductInterface $fetchUserRightsOnProduct,
        private TokenStorageInterface $tokenStorage,
        private SecurityFacadeInterface $security,
    ) {
    }

    /**
     * @throws ResourceAccessDeniedException If the user has ownership on the product
     *                                       Or if user has only view permission on the product
     * @throws NotFoundHttpException         If the product does not exist
     *                                       Or if there is no draft created for the product and the current user
     */
    public function __invoke(Request $request, string $uuid): Response
    {
        if (!$this->security->isGranted('pim_api_product_list')) {
            throw new AccessDeniedHttpException('Access forbidden. You are not allowed to list products.');
        }

        $uuid = Uuid::fromString($uuid)->toString();

        try {
            $product = $this->productRepository->find($uuid);
        } catch (ResourceAccessDeniedException) {
            throw new NotFoundHttpException(\sprintf('Product "%s" does not exist or you do not have permission to access it.', $uuid));
        }

        if (null === $product) {
            throw new NotFoundHttpException(\sprintf('Product "%s" does not exist or you do not have permission to access it.', $uuid));
        }

        $user = $this->tokenStorage->getToken()?->getUser();
        Assert::isInstanceOf($user, UserInterface::class);

        $userRights = $this->fetchUserRightsOnProduct->fetchByUuid($product->getUuid(), (int) $user->getId());

        if ($userRights->isProductEditable()) {
            throw new ResourceAccessDeniedException($product, sprintf(
                'You have ownership on the product "%s", you cannot create or retrieve a draft from this product.',
                $uuid
            ));
        } elseif (!$userRights->canApplyDraftOnProduct()) {
            throw new ResourceAccessDeniedException($product, sprintf(
                'You only have view permission on the product "%s", you cannot create or retrieve a draft from this product.',
                $uuid
            ));
        }

        $productDraft = $this->productDraftRepository->findUserEntityWithValuesDraft($product, $user->getUsername());
        if (null === $productDraft) {
            throw new NotFoundHttpException(sprintf('There is no draft created for the product "%s".', $uuid));
        }
        $this->productDraftApplier->applyAllChanges($product, $productDraft);

        return new JsonResponse($this->normalizer->normalize($product, 'external_api', ['userId' => $user->getId()]));
    }
}
