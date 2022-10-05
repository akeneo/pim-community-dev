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

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\DescendantProductModelIdsQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\DescendantProductUuidsQueryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Indexer\ProductModelProposalIndexer;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Indexer\ProductProposalIndexer;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\SelectModelProposalIdsFromProductModelIdsQueryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\SelectProposalIdsFromProductUuidsQueryInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class RemoveModelProposalsIndexSubscriber implements EventSubscriberInterface
{
    /** @var int[] */
    private $proposalModelIdsToDelete = [];

    /** @var int[] */
    private $proposalIdsToDelete = [];

    public function __construct(
        private SelectModelProposalIdsFromProductModelIdsQueryInterface $selectModelProposalIdsFromProductModelIdsQuery,
        private SelectProposalIdsFromProductUuidsQueryInterface $selectProposalIdsFromProductIdsQuery,
        private DescendantProductUuidsQueryInterface $descendantProductUuidsQuery,
        private DescendantProductModelIdsQueryInterface $descendantProductModelIdsQuery,
        private ProductModelProposalIndexer $productModelProposalIndexer,
        private ProductProposalIndexer $productProposalIndexer
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_REMOVE => ['calculateImpactedModelProposals', 300],
            StorageEvents::POST_REMOVE => ['removeProductModelProposals', 300],
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function calculateImpactedModelProposals(GenericEvent $event)
    {
        if (!$event->getSubject() instanceof ProductModelInterface) {
            return;
        }

        $productModelId = $event->getSubject()->getId();
        $productModelIds = $this->descendantProductModelIdsQuery->fetchFromParentProductModelId($productModelId);
        $productModelIds[] = $productModelId;

        $productUuids = $this->descendantProductUuidsQuery->fetchFromProductModelIds($productModelIds);

        $this->proposalModelIdsToDelete = $this->selectModelProposalIdsFromProductModelIdsQuery->fetch($productModelIds);
        $this->proposalIdsToDelete = $this->selectProposalIdsFromProductIdsQuery->fetch($productUuids);
    }

    /**
     * As the model proposals are already deleted from database using cascade delete
     * We "only" need to remove model proposals from ES index
     *
     * @param GenericEvent $event
     */
    public function removeProductModelProposals(GenericEvent $event)
    {
        if (!empty($this->proposalModelIdsToDelete)) {
            $this->productModelProposalIndexer->removeAll($this->proposalModelIdsToDelete);
            $this->proposalModelIdsToDelete = [];
        }

        if (!empty($this->proposalIdsToDelete)) {
            $this->productProposalIndexer->removeAll($this->proposalIdsToDelete);
            $this->proposalIdsToDelete = [];
        }
    }
}
