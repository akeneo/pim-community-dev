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

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\ProposalTracking;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProposalTracking;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\SelectAttributeLabelsFromCodesQueryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\ProposalTrackingRepositoryInterface;

class TrackProposal
{
    /** @var SelectAttributeLabelsFromCodesQueryInterface */
    private $selectAttributeLabelsQuery;

    /** @var ProposalTrackingRepositoryInterface */
    private $proposalTrackingRepository;

    public function __construct(
        SelectAttributeLabelsFromCodesQueryInterface $selectAttributeLabelsQuery,
        ProposalTrackingRepositoryInterface $proposalTrackingRepository
    ) {
        $this->selectAttributeLabelsQuery = $selectAttributeLabelsQuery;
        $this->proposalTrackingRepository = $proposalTrackingRepository;
    }

    public function track(
        EntityWithValuesDraftInterface $draft,
        string $proposalStatus,
        array $attributeCodes,
        string $comment
    ): void {
        $entityType = $this->getEntityType($draft);
        $payload = [
            'creation_date' => $draft->getCreatedAt()->format('Y-m-d H:i:s'),
            'source_code' => $draft->getSource(),
            'author_label' => $draft->getAuthorLabel(),
            'status' => $proposalStatus,
            'attributes' => $this->selectAttributeLabelsQuery->execute($attributeCodes),
            'comment' => $comment,
        ];

        $proposalTracking = new ProposalTracking(
            $entityType,
            $draft->getEntityWithValue()->getId(),
            new \DateTime(),
            $payload
        );

        $this->proposalTrackingRepository->save($proposalTracking);
    }

    private function getEntityType(EntityWithValuesDraftInterface $draft): string
    {
        return $draft instanceof ProductModelDraft ? ProposalTracking::TYPE_PRODUCT_MODEL : ProposalTracking::TYPE_PRODUCT;
    }
}
