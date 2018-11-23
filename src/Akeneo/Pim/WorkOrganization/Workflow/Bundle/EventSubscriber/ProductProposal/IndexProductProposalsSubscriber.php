<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\ProductProposal;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Indexer\ProductProposalIndexer;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Event\EntityWithValuesDraftEvents;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class IndexProductProposalsSubscriber implements EventSubscriberInterface
{
    /** @var ProductProposalIndexer */
    protected $productProposalIndexer;

    /**
     * @param ProductProposalIndexer $productProposalIndexer
     */
    public function __construct(ProductProposalIndexer $productProposalIndexer)
    {
        $this->productProposalIndexer = $productProposalIndexer;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::POST_SAVE => ['indexProductProposal', 300],
            StorageEvents::POST_SAVE_ALL => ['bulkIndexProductProposals', 300],
            StorageEvents::POST_REMOVE => ['deleteProductProposal', 300],
            EntityWithValuesDraftEvents::POST_REFUSE => ['deleteProductProposal', 300],
        ];
    }

    /**
     * Index one single product proposal.
     * If there is no values to review, remove the product proposal in the index.
     *
     * @param GenericEvent $event
     */
    public function indexProductProposal(GenericEvent $event)
    {
        $productProposal = $event->getSubject();
        if (!$productProposal instanceof ProductDraft) {
            return;
        }

        if (!$event->hasArgument('unitary') || false === $event->getArgument('unitary')) {
            return;
        }

        if ($productProposal instanceof ProductDraft) {
            $changesToReview = $productProposal->getChangesToReview();
            if (!empty($changesToReview['values'])) {
                $productProposal->setChanges($changesToReview);
                $this->productProposalIndexer->index($productProposal);
            } else {
                $this->productProposalIndexer->remove($productProposal->getId());
            }
        }
    }

    /**
     * Index several product proposals at a time.
     *
     * @param GenericEvent $event
     */
    public function bulkIndexProductProposals(GenericEvent $event)
    {
        $productProposals = $event->getSubject();
        if (!is_array($productProposals)) {
            return;
        }

        if (!current($productProposals) instanceof ProductDraft) {
            return;
        }

        $proposalsToIndex = [];
        $proposalsToRemove = [];
        foreach ($productProposals as $productProposal) {
            $changesToReview = $productProposal->getChangesToReview();
            if (!empty($changesToReview['values'])) {
                $productProposal->setChanges($changesToReview);
                $proposalsToIndex[] = $productProposal;
            } else {
                $proposalsToRemove[] = $productProposal;
            }
        }

        if (!empty($proposalsToIndex)) {
            $this->productProposalIndexer->indexAll($proposalsToIndex);
        }

        if (!empty($proposalsToRemove)) {
            $this->productProposalIndexer->removeAll($proposalsToRemove);
        }
    }

    /**
     * Delete one single product proposal.
     *
     * @param RemoveEvent $event
     */
    public function deleteProductProposal(GenericEvent $event)
    {
        $productProposal = $event->getSubject();
        if (!$productProposal instanceof ProductDraft ||
            $productProposal->getStatus() === EntityWithValuesDraftInterface::IN_PROGRESS) {
            return;
        }

        if ($event instanceof RemoveEvent) {
            $this->productProposalIndexer->remove($event->getSubjectId());
        } elseif ($event instanceof GenericEvent) {
            $this->productProposalIndexer->remove($event->getSubject()->getId());
        }
    }
}
