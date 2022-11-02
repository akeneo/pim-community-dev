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
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Manager\EntityWithValuesDraftManager;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Webmozart\Assert\Assert;

class ProductModelProposalController
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $productModelRepository;

    /** @var EntityWithValuesDraftRepositoryInterface */
    protected $productModelDraftRepository;

    /** @var EntityWithValuesDraftManager */
    protected $draftManager;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /**
     * @param IdentifiableObjectRepositoryInterface    $productModelRepository
     * @param EntityWithValuesDraftRepositoryInterface $productModelDraftRepository
     * @param EntityWithValuesDraftManager             $draftManager
     * @param TokenStorageInterface                    $tokenStorage
     * @param AuthorizationCheckerInterface            $authorizationChecker
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $productModelRepository,
        EntityWithValuesDraftRepositoryInterface $productModelDraftRepository,
        EntityWithValuesDraftManager $draftManager,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        private SecurityFacadeInterface $security,
    ) {
        $this->productModelRepository = $productModelRepository;
        $this->productModelDraftRepository = $productModelDraftRepository;
        $this->draftManager = $draftManager;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * Submit a product model draft proposal.
     *
     * @throws NotFoundHttpException            If the product does not exist
     * @throws ResourceAccessDeniedException    If the user has ownership on the product
     *                                          Or if user has only view permission on the product
     * @throws UnprocessableEntityHttpException If there is no draft on the product
     *                                          Or if the proposal has already been submitted
     */
    public function createAction(Request $request, string $code): Response
    {
        $this->denyAccessUnlessAclIsGranted();

        $decodedContent = json_decode($request->getContent(), true);
        if (null === $decodedContent) {
            throw new BadRequestHttpException('Invalid json message received.');
        }

        $productModel = $this->productModelRepository->findOneByIdentifier($code);
        if (null === $productModel) {
            throw new NotFoundHttpException(sprintf('Product model "%s" does not exist.', $code));
        }

        $this->userHasOwnPermissions($productModel, $code);
        $this->userHasNotEditPermissions($productModel, $code);

        $userToken = $this->tokenStorage->getToken();
        $productModelDraft = $this->productModelDraftRepository->findUserEntityWithValuesDraft($productModel, $userToken->getUserIdentifier());
        if (null === $productModelDraft) {
            throw new UnprocessableEntityHttpException('You should create a draft before submitting it for approval.');
        }

        Assert::isInstanceOf($productModelDraft, EntityWithValuesDraftInterface::class);
        if (ProductModelDraft::READY === $productModelDraft->getStatus()) {
            throw new UnprocessableEntityHttpException('You already submit your draft for approval.');
        }

        $this->draftManager->markAsReady($productModelDraft);

        return new Response(null, Response::HTTP_CREATED);
    }

    private function userHasOwnPermissions(ProductModelInterface $productModel, string $code): void
    {
        $isOwner = $this->authorizationChecker->isGranted(Attributes::OWN, $productModel);

        if ($isOwner) {
            throw new ResourceAccessDeniedException($productModel, sprintf(
                'You have ownership on the product model "%s", you cannot send a draft for approval.',
                $code
            ));
        }
    }

    private function userHasNotEditPermissions(ProductModelInterface $productModel, string $code): void
    {
        $canEdit = $this->authorizationChecker->isGranted(Attributes::EDIT, $productModel);

        if (!$canEdit) {
            throw new ResourceAccessDeniedException($productModel, sprintf(
                'You only have view permission on the product model "%s", you cannot send a draft for approval.',
                $code
            ));
        }
    }

    private function denyAccessUnlessAclIsGranted(): void
    {
        if (!$this->security->isGranted('pim_api_product_edit')) {
            throw new AccessDeniedHttpException('Access forbidden. You are not allowed to create or update products.');
        }
    }
}
