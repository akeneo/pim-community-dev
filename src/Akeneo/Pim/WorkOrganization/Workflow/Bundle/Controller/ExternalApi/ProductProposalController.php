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

use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Permission\Component\Exception\ResourceAccessDeniedException;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Manager\EntityWithValuesDraftManager;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
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

/**
 * @author Laurent Petard <laurent.petard@akeneo.com>
 */
class ProductProposalController
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $productRepository;

    /** @var EntityWithValuesDraftRepositoryInterface */
    protected $productDraftRepository;

    /** @var EntityWithValuesDraftManager */
    protected $productDraftManager;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /**
     * @param IdentifiableObjectRepositoryInterface    $productRepository
     * @param EntityWithValuesDraftRepositoryInterface $productDraftRepository
     * @param EntityWithValuesDraftManager             $productDraftManager
     * @param TokenStorageInterface                    $tokenStorage
     * @param AuthorizationCheckerInterface            $authorizationChecker
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $productRepository,
        EntityWithValuesDraftRepositoryInterface $productDraftRepository,
        EntityWithValuesDraftManager $productDraftManager,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        private SecurityFacadeInterface $security,
    ) {
        $this->productRepository = $productRepository;
        $this->productDraftRepository = $productDraftRepository;
        $this->productDraftManager = $productDraftManager;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * Submit a product draft proposal.
     *
     * @param Request $request
     * @param string  $code
     *
     * @throws NotFoundHttpException            If the product does not exist
     * @throws ResourceAccessDeniedException    If the user has ownership on the product
     *                                          Or if user has only view permission on the product
     * @throws UnprocessableEntityHttpException If there is no draft on the product
     *                                          Or if the proposal has already been submitted
     *
     * @return Response
     */
    public function createAction(Request $request, string $code): Response
    {
        $this->denyAccessUnlessAclIsGranted();

        $this->ensureRequestBodyIsValid($request);

        $product = $this->productRepository->findOneByIdentifier($code);

        if (null === $product) {
            throw new NotFoundHttpException(sprintf('Product "%s" does not exist.', $code));
        }

        $isOwner = $this->authorizationChecker->isGranted(Attributes::OWN, $product);

        if ($isOwner) {
            throw new ResourceAccessDeniedException($product, sprintf(
                'You have ownership on the product "%s", you cannot send a draft for approval.',
                $code
            ));
        }

        $canEdit = $this->authorizationChecker->isGranted(Attributes::EDIT, $product);

        if (!$canEdit) {
            throw new ResourceAccessDeniedException($product, sprintf(
                'You only have view permission on the product "%s", you cannot send a draft for approval.',
                $code
            ));
        }

        $userToken = $this->tokenStorage->getToken();
        $productDraft = $this->productDraftRepository->findUserEntityWithValuesDraft($product, $userToken->getUserIdentifier());

        if (null === $productDraft) {
            throw new UnprocessableEntityHttpException('You should create a draft before submitting it for approval.');
        }

        Assert::isInstanceOf($productDraft, EntityWithValuesDraftInterface::class);
        if (ProductDraft::READY === $productDraft->getStatus()) {
            throw new UnprocessableEntityHttpException('You already submit your draft for approval.');
        }

        $this->productDraftManager->markAsReady($productDraft);

        return new Response(null, Response::HTTP_CREATED);
    }

    /**
     * @param Request $request
     *
     * @throws BadRequestHttpException If the request's body is an invalid json.
     */
    private function ensureRequestBodyIsValid(Request $request): void
    {
        $decodedContent = json_decode($request->getContent(), true);

        if (null === $decodedContent) {
            throw new BadRequestHttpException('Invalid json message received.');
        }
    }

    private function denyAccessUnlessAclIsGranted(): void
    {
        if (!$this->security->isGranted('pim_api_product_edit')) {
            throw new AccessDeniedHttpException('Access forbidden. You are not allowed to create or update products.');
        }
    }
}
