<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductModelProposal;

use Akeneo\Component\StorageUtils\StorageEvents;
use PimEnterprise\Bundle\WorkflowBundle\Elasticsearch\Indexer\ProductModelProposalIndexer;
use PimEnterprise\Bundle\WorkflowBundle\Elasticsearch\Indexer\ProductProposalIndexer;
use PimEnterprise\Component\Workflow\Event\EntityWithValuesDraftEvents;
use PimEnterprise\Component\Workflow\Model\EntityWithValuesDraftInterface;
use PimEnterprise\Component\Workflow\Model\ProductModelDraft;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Philippe Mossière <philippe.mossiere@akeneo.com>
 */
class IndexProductModelProposalsSubscriber implements EventSubscriberInterface
{
    /** @var ProductModelProposalIndexer */
    private $productModelProposalIndexer;

    public function __construct(ProductModelProposalIndexer $productProposalIndexer)
    {
        $this->productModelProposalIndexer = $productProposalIndexer;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::POST_SAVE => ['indexProductModelProposal', 300],
        ];
    }

    public function indexProductModelProposal(GenericEvent $event): void
    {
        $productProposal = $event->getSubject();
        if (!$productProposal instanceof ProductModelDraft) {
            return;
        }

        if (!$event->hasArgument('unitary') || false === $event->getArgument('unitary')) {
            return;
        }

        if ($productProposal instanceof ProductModelDraft) {
            $changesToReview = $productProposal->getChangesToReview();
            if (!empty($changesToReview['values'])) {
                $productProposal->setChanges($changesToReview);
                $this->productModelProposalIndexer->index($productProposal);
            } else {
                $this->productModelProposalIndexer->remove($productProposal->getId());
            }
        }
    }
}
