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

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\Product;

use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use PimEnterprise\Bundle\WorkflowBundle\Elasticsearch\Indexer\ProductProposalIndexer;
use PimEnterprise\Component\Workflow\Query\SelectProposalIdsFromProductIdsQueryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class RemoveProposalsIndexSubscriber implements EventSubscriberInterface
{
    /** @var SelectProposalIdsFromProductIdsQueryInterface  */
    private $selectProposalIdsFromProductIdsQuery;

    /** @var ProductProposalIndexer */
    private $productProposalIndexer;

    /** @var int[] */
    private $proposalIdsToDelete = [];

    public function __construct(
        SelectProposalIdsFromProductIdsQueryInterface $selectProposalIdsFromProductIdQuery,
        ProductProposalIndexer $productProposalIndexer
    ) {
        $this->productProposalIndexer = $productProposalIndexer;
        $this->selectProposalIdsFromProductIdsQuery = $selectProposalIdsFromProductIdQuery;
    }

    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_REMOVE => ['calculateImpactedProposals', 300],
            StorageEvents::POST_REMOVE => ['removeProductProposals', 300],
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function calculateImpactedProposals(GenericEvent $event)
    {
        if (!$event->getSubject() instanceof ProductInterface) {
            return;
        }

        $this->proposalIdsToDelete = $this->selectProposalIdsFromProductIdsQuery->fetch([$event->getSubject()->getId()]);
    }

    /**
     * As the proposals are already deleted from database using cascade delete
     * We "only" need to remove proposals from ES index
     *
     * @param GenericEvent $event
     */
    public function removeProductProposals(GenericEvent $event)
    {
        if (!empty($this->proposalIdsToDelete)) {
            $this->productProposalIndexer->removeAll($this->proposalIdsToDelete);
            $this->proposalIdsToDelete = [];
        }
    }
}
