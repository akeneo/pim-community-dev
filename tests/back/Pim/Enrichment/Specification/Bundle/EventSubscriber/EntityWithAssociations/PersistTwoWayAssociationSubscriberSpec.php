<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EntityWithAssociations;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Updater\TwoWayAssociationUpdater;
use Akeneo\Pim\Enrichment\Component\Product\Association\MissingAssociationAdder;
use Akeneo\Pim\Enrichment\Component\Product\Association\TwoWayAssociationsIndexationQueue;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductIndexerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductModelIndexerInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PersistTwoWayAssociationSubscriberSpec extends ObjectBehavior
{
    public function let(
        ManagerRegistry $registry,
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        TwoWayAssociationsIndexationQueue $indexationQueue
    ) {
        $this->beConstructedWith($registry, $productIndexer, $productModelIndexer, $indexationQueue);
    }

    public function it_is_an_event_subscriber(): void
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_subscribes_to_pre_save_and_post_save(): void
    {
        $this::getSubscribedEvents()->shouldHaveKey(StorageEvents::PRE_SAVE);
        $this::getSubscribedEvents()->shouldHaveKey(StorageEvents::POST_SAVE);
    }

    /**
     * This reproduces the reported bug end to end: removing a two-way association queues the
     * reciprocal entity via TwoWayAssociationUpdater, and the subscriber must flush that same
     * queue into the product indexer on POST_SAVE, so the reciprocal entity's ES document is
     * actually refreshed instead of only "queued".
     */
    public function it_reindexes_the_reciprocal_product_after_a_two_way_association_is_removed_by_the_updater(
        ManagerRegistry $registry,
        EntityManager $entityManager,
        MissingAssociationAdder $missingAssociationAdder,
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer
    ): void {
        $registry->getManager()->willReturn($entityManager);

        $indexationQueue = new TwoWayAssociationsIndexationQueue();
        $this->beConstructedWith($registry, $productIndexer, $productModelIndexer, $indexationQueue);

        $updater = new TwoWayAssociationUpdater(
            $registry->reveal(),
            $missingAssociationAdder->reveal(),
            $indexationQueue
        );

        $owner = new Product();
        $reciprocalProduct = new Product();

        $entityManager->persist($reciprocalProduct)->shouldBeCalled();

        $updater->removeInversedAssociation($owner, 'xsell', $reciprocalProduct);

        $productIndexer->indexFromProductUuids([$reciprocalProduct->getUuid()])->shouldBeCalled();
        $productModelIndexer->indexFromProductModelCodes([])->shouldBeCalled();

        $this->indexAssociatedEntities();
    }
}
