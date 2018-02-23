<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductProposal;

use Akeneo\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Elasticsearch\Indexer\ProductProposalIndexer;
use PimEnterprise\Component\Workflow\Event\ProductDraftEvents;
use PimEnterprise\Component\Workflow\Model\ProductDraftInterface;
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
            ProductDraftEvents::POST_REFUSE => ['deleteProductProposal', 300],
        ];
    }

    /**
     * Index one single product or published product.
     *
     * @param GenericEvent $event
     */
    public function indexProductProposal(GenericEvent $event)
    {
        $productProposal = $event->getSubject();
        if (!$productProposal instanceof ProductDraftInterface) {
            return;
        }

        if (!$event->hasArgument('unitary') || false === $event->getArgument('unitary')) {
            return;
        }

        if ($productProposal instanceof ProductDraftInterface && $productProposal->getStatus() === ProductDraftInterface::READY) {
            $this->productProposalIndexer->index($productProposal);
        }
    }

    /**
     * Index several products or published products at a time.
     *
     * @param GenericEvent $event
     */
    public function bulkIndexProductProposals(GenericEvent $event)
    {
        $productProposals = $event->getSubject();
        if (!is_array($productProposals)) {
            return;
        }

        if (!current($productProposals) instanceof ProductInterface) {
            return;
        }

        if (current($productProposals) instanceof ProductDraftInterface && current($productProposals)->getStatus() === ProductDraftInterface::READY) {
            $this->productProposalIndexer->index($productProposals);
        }
    }

    /**
     * Delete one single product or published product from the right ES index
     *
     * @param RemoveEvent $event
     */
    public function deleteProductProposal(GenericEvent $event)
    {
        $productProposal = $event->getSubject();
        if (!$productProposal instanceof ProductDraftInterface ||
            $productProposal->getStatus() === ProductDraftInterface::IN_PROGRESS) {
            return;
        }

        if ($event instanceof RemoveEvent) {
            $this->productProposalIndexer->remove($event->getSubjectId());
        } elseif ($event instanceof GenericEvent) {
            $this->productProposalIndexer->remove($event->getSubject()->getId());
        }
    }
}
