<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductAndAncestorsIndexer;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductIndexerInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\IndexProductsSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class IndexProductsSubscriberSpec extends ObjectBehavior
{
    function let(
        ProductIndexerInterface $indexer,
        ProductAndAncestorsIndexer $productAndAncestorsIndexer
    ) {
        $this->beConstructedWith($indexer, $productAndAncestorsIndexer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IndexProductsSubscriber::class);
    }

    function it_subscribes_to_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            StorageEvents::POST_SAVE     => ['indexProduct', 300],
            StorageEvents::POST_SAVE_ALL => ['bulkIndexProducts', 300],
            StorageEvents::POST_REMOVE   => ['deleteProduct', 300],
        ]);
    }

    function it_indexes_a_single_product(
        ProductAndAncestorsIndexer $productAndAncestorsIndexer,
        GenericEvent $event,
        ProductInterface $product
    ) {
        $event->getSubject()->willReturn($product);
        $event->hasArgument('unitary')->willReturn(true);
        $event->getArgument('unitary')->willReturn(true);

        $product->getIdentifier()->willReturn('identifier');

        $productAndAncestorsIndexer->indexFromProductIdentifiers(['identifier'])->shouldBeCalled();

        $this->indexProduct($event);
    }

    function it_bulk_indexes_products(
        ProductAndAncestorsIndexer $productAndAncestorsIndexer,
        GenericEvent $event,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $event->getSubject()->willReturn([$product1, $product2]);

        $product1->getIdentifier()->willReturn('identifier1');
        $product2->getIdentifier()->willReturn('identifier2');

        $productAndAncestorsIndexer->indexFromProductIdentifiers(['identifier1', 'identifier2'])->shouldBeCalled();

        $this->bulkIndexProducts($event);
    }

    function it_delete_product_from_elasticsearch_index(
        ProductIndexerInterface $indexer,
        RemoveEvent $event,
        ProductInterface $product
    ) {
        $event->getSubject()->willReturn($product);
        $event->getSubjectId()->willReturn(40);

        $indexer->removeFromProductId(40)->shouldBeCalled();

        $this->deleteProduct($event)->shouldReturn(null);
    }

    function it_does_not_index_a_non_unitary_save_of_a_product(
        ProductAndAncestorsIndexer $productAndAncestorsIndexer,
        GenericEvent $event,
        ProductInterface $product
    ) {
        $event->getSubject()->willReturn($product);
        $event->hasArgument('unitary')->willReturn(true);
        $event->getArgument('unitary')->willReturn(false);

        $productAndAncestorsIndexer->indexFromProductIdentifiers(Argument::any())->shouldNotBeCalled();

        $this->indexProduct($event);
    }

    function it_does_not_index_a_non_unitary_save_of_a_product_bis(
        ProductAndAncestorsIndexer $productAndAncestorsIndexer,
        GenericEvent $event,
        ProductInterface $product
    ) {
        $event->getSubject()->willReturn($product);
        $event->hasArgument('unitary')->willReturn(false);

        $productAndAncestorsIndexer->indexFromProductIdentifiers(Argument::any())->shouldNotBeCalled();

        $this->indexProduct($event);
    }

    function it_does_not_index_a_non_product_entity(
        ProductAndAncestorsIndexer $productAndAncestorsIndexer
    ) {
        $productAndAncestorsIndexer->indexFromProductIdentifiers(Argument::any())->shouldNotBeCalled();

        $this->indexProduct(new GenericEvent(new \stdClass()));
    }

    function it_does_not_bulk_index_non_product_entities(
        ProductAndAncestorsIndexer $productAndAncestorsIndexer,
        GenericEvent $event,
        \stdClass $subject1
    ) {
        $event->getSubject()->willReturn([$subject1]);

        $productAndAncestorsIndexer->indexFromProductIdentifiers(Argument::any())->shouldNotBeCalled();

        $this->bulkIndexProducts($event);
    }

    function it_does_not_bulk_index_non_collections(
        ProductAndAncestorsIndexer $productAndAncestorsIndexer
    ) {
        $productAndAncestorsIndexer->indexFromProductIdentifiers(Argument::any())->shouldNotBeCalled();

        $this->bulkIndexProducts(new GenericEvent(new \stdClass()));
    }

    function it_does_not_delete_non_product_entity_from_elasticsearch(
        ProductIndexerInterface $indexer
    ) {
        $indexer->removeFromProductId(Argument::any())->shouldNotBeCalled();

        $this->deleteProduct(new RemoveEvent(new \stdClass(), 40))->shouldReturn(null);
    }
}
