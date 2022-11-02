<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\ProductModelProposal;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Indexer\ProductModelProposalIndexer;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Event\EntityWithValuesDraftEvents;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Philippe Mossière <philippe.mossiere@akeneo.com>
 */
class IndexProductModelProposalsSubscriber implements EventSubscriberInterface
{
    /** @var ProductModelProposalIndexer */
    private $productModelProposalIndexer;

    public function __construct(ProductModelProposalIndexer $productModelProposalIndexer)
    {
        $this->productModelProposalIndexer = $productModelProposalIndexer;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE => ['indexProductModelProposal', 300],
            StorageEvents::POST_SAVE_ALL => ['bulkIndexProductModelProposals', 300],
            StorageEvents::POST_REMOVE => ['deleteProductModelProposal', 300],
            EntityWithValuesDraftEvents::POST_REFUSE => ['deleteProductModelProposal', 300],
        ];
    }

    public function indexProductModelProposal(GenericEvent $event): void
    {
        $productModelProposal = $event->getSubject();
        if (!$productModelProposal instanceof ProductModelDraft) {
            return;
        }

        if (!$event->hasArgument('unitary') || false === $event->getArgument('unitary')) {
            return;
        }

        $changesToReview = $productModelProposal->getChangesToReview();
        if (!empty($changesToReview['values'])) {
            $productModelProposal->setChanges($changesToReview);
            $this->productModelProposalIndexer->index($productModelProposal);
        } else {
            $this->productModelProposalIndexer->remove($productModelProposal->getId());
        }
    }

    /**
     * Index several product model proposals at a time.
     *
     * @param GenericEvent $event
     */
    public function bulkIndexProductModelProposals(GenericEvent $event)
    {
        $productModelProposals = $event->getSubject();
        if (!is_array($productModelProposals)) {
            return;
        }

        if (!current($productModelProposals) instanceof ProductModelDraft) {
            return;
        }

        $proposalsToIndex = [];
        $proposalsToRemove = [];
        foreach ($productModelProposals as $productModelProposal) {
            $changesToReview = $productModelProposal->getChangesToReview();
            if (!empty($changesToReview['values'])) {
                $productModelProposal->setChanges($changesToReview);
                $proposalsToIndex[] = $productModelProposal;
            } else {
                $proposalsToRemove[] = $productModelProposal;
            }
        }

        if (!empty($proposalsToIndex)) {
            $this->productModelProposalIndexer->indexAll($proposalsToIndex);
        }

        if (!empty($proposalsToRemove)) {
            $this->productModelProposalIndexer->removeAll($proposalsToRemove);
        }
    }

    /**
     * Delete one single product model proposal.
     *
     * @param RemoveEvent $event
     */
    public function deleteProductModelProposal(GenericEvent $event)
    {
        $productModelProposal = $event->getSubject();
        if (!$productModelProposal instanceof ProductModelDraft ||
            $productModelProposal->getStatus() === EntityWithValuesDraftInterface::IN_PROGRESS) {
            return;
        }

        if ($event instanceof RemoveEvent) {
            $this->productModelProposalIndexer->remove($event->getSubjectId());
        } elseif ($event instanceof GenericEvent) {
            $this->productModelProposalIndexer->remove($event->getSubject()->getId());
        }
    }
}
