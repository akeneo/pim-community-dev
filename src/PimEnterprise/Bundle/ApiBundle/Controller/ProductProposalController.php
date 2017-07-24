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

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Manager\ProductDraftManager;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException;
use PimEnterprise\Component\Workflow\Model\ProductDraft;
use PimEnterprise\Component\Workflow\Repository\ProductDraftRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author Laurent Petard <laurent.petard@akeneo.com>
 */
class ProductProposalController
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $productRepository;

    /** @var ProductDraftRepositoryInterface */
    protected $productDraftRepository;

    /** @var ProductDraftManager */
    protected $productDraftManager;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /**
     * @param IdentifiableObjectRepositoryInterface $productRepository
     * @param ProductDraftRepositoryInterface       $productDraftRepository
     * @param ProductDraftManager                   $productDraftManager
     * @param TokenStorageInterface                 $tokenStorage
     * @param AuthorizationCheckerInterface         $authorizationChecker
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $productRepository,
        ProductDraftRepositoryInterface $productDraftRepository,
        ProductDraftManager $productDraftManager,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker
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
        $productDraft = $this->productDraftRepository->findUserProductDraft($product, $userToken->getUsername());

        if (null === $productDraft) {
            throw new UnprocessableEntityHttpException('You should create a draft before submitting it for approval.');
        }

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
}
