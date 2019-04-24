<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Storage\ORM\Connector\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\GetMetadataInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class GetMetadata implements GetMetadataInterface
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
    private $productModelDraftRepository;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        EntityWithValuesDraftRepositoryInterface $productModelDraftRepository
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
        $this->productModelDraftRepository = $productModelDraftRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function forProductModel(ProductModelInterface $productModel): array
    {
        $isOwner = $this->authorizationChecker->isGranted(Attributes::OWN, $productModel);

        if ($isOwner) {
            return ['workflow_status' => static::WORKFLOW_STATUS_WORKING_COPY];
        }

        $canEdit = $this->authorizationChecker->isGranted(Attributes::EDIT, $productModel);

        if ($canEdit) {
            $userName = $this->tokenStorage->getToken()->getUsername();
            $productDraft = $this->productModelDraftRepository->findUserEntityWithValuesDraft($productModel, $userName);

            if (null === $productDraft) {
                return ['workflow_status' => static::WORKFLOW_STATUS_WORKING_COPY];
            }

            if (EntityWithValuesDraftInterface::READY === $productDraft->getStatus()) {
                return ['workflow_status' => static::WORKFLOW_STATUS_WAITING_FOR_APPROVAL];
            }

            return ['workflow_status' => static::WORKFLOW_STATUS_IN_PROGRESS];
        }

        $canView = $this->authorizationChecker->isGranted(Attributes::VIEW, $productModel);

        if ($canView) {
            return ['workflow_status' => static::WORKFLOW_STATUS_READ_ONLY];
        }

        throw new \LogicException(
            'A product model should not be normalized if the user has not the "view" permission on it.'
        );
    }
}
