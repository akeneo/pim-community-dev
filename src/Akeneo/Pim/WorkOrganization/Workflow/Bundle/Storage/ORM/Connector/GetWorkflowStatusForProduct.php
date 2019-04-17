<?php

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Storage\ORM\Connector;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class GetWorkflowStatusForProduct
{
    private const WORKFLOW_STATUS_WORKING_COPY = 'working_copy';
    private const WORKFLOW_STATUS_READ_ONLY = 'read_only';
    private const WORKFLOW_STATUS_IN_PROGRESS = 'draft_in_progress';
    private const WORKFLOW_STATUS_WAITING_FOR_APPROVAL = 'proposal_waiting_for_approval';

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var EntityWithValuesDraftRepositoryInterface */
    private $productDraftRepository;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        EntityWithValuesDraftRepositoryInterface $productDraftRepository
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
        $this->productDraftRepository = $productDraftRepository;
    }

    /**
     * @throws \LogicException If the user has not even the "view" permission on the product.
     */
    public function fromProduct(ProductInterface $product): string
    {
        $isOwner = $this->authorizationChecker->isGranted(Attributes::OWN, $product);

        if ($isOwner) {
            return static::WORKFLOW_STATUS_WORKING_COPY;
        }

        $canEdit = $this->authorizationChecker->isGranted(Attributes::EDIT, $product);

        if ($canEdit) {
            $userName = $this->tokenStorage->getToken()->getUsername();
            $productDraft = $this->productDraftRepository->findUserEntityWithValuesDraft($product, $userName);

            if (null === $productDraft) {
                return static::WORKFLOW_STATUS_WORKING_COPY;
            }

            if (EntityWithValuesDraftInterface::READY === $productDraft->getStatus()) {
                return static::WORKFLOW_STATUS_WAITING_FOR_APPROVAL;
            }

            return static::WORKFLOW_STATUS_IN_PROGRESS;
        }

        $canView = $this->authorizationChecker->isGranted(Attributes::VIEW, $product);

        if ($canView) {
            return static::WORKFLOW_STATUS_READ_ONLY;
        }

        throw new \LogicException('A product should not be normalized if the user has not the "view" permission on it.');
    }
}
