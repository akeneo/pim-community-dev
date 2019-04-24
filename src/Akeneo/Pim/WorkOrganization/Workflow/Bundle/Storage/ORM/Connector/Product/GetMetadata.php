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

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Storage\ORM\Connector\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetMetadataInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @author    Mathias Métayer <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class GetMetadata implements GetMetadataInterface
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
     * {@inheritdoc}
     */
    public function forProduct(ProductInterface $product): array
    {
        $isOwner = $this->authorizationChecker->isGranted(Attributes::OWN, $product);

        if ($isOwner) {
            return ['workflow_status' => static::WORKFLOW_STATUS_WORKING_COPY];
        }

        $canEdit = $this->authorizationChecker->isGranted(Attributes::EDIT, $product);

        if ($canEdit) {
            $userName = $this->tokenStorage->getToken()->getUsername();
            $productDraft = $this->productDraftRepository->findUserEntityWithValuesDraft($product, $userName);

            if (null === $productDraft) {
                return ['workflow_status' => static::WORKFLOW_STATUS_WORKING_COPY];
            }

            if (EntityWithValuesDraftInterface::READY === $productDraft->getStatus()) {
                return ['workflow_status' => static::WORKFLOW_STATUS_WAITING_FOR_APPROVAL];
            }

            return ['workflow_status' => static::WORKFLOW_STATUS_IN_PROGRESS];
        }

        $canView = $this->authorizationChecker->isGranted(Attributes::VIEW, $product);

        if ($canView) {
            return ['workflow_status' => static::WORKFLOW_STATUS_READ_ONLY];
        }

        throw new \LogicException('A product should not be normalized if the user has not the "view" permission on it.');
    }
}
