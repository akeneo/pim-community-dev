<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber;

use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\Indexer\ProductIndexerInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\IndexProductsSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class IndexProductsSubscriberSpec extends ObjectBehavior
{
    function let(ProductIndexerInterface $indexer)
    {
        $this->beConstructedWith($indexer);
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

    function it_indexes_a_single_product($indexer, GenericEvent $event, ProductInterface $product)
    {
        $event->getSubject()->willReturn($product);
        $event->hasArgument('unitary')->willReturn(true);
        $event->getArgument('unitary')->willReturn(true);

        $product->getIdentifier()->willReturn('identifier');

        $indexer->indexFromProductIdentifier('identifier')->shouldBeCalled();

        $this->indexProduct($event);
    }

    function it_bulk_indexes_products(
        $indexer,
        GenericEvent $event,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $event->getSubject()->willReturn([$product1, $product2]);

        $product1->getIdentifier()->willReturn('identifier1');
        $product2->getIdentifier()->willReturn('identifier2');

        $indexer->indexFromProductIdentifiers(['identifier1', 'identifier2'])->shouldBeCalled();

        $this->bulkIndexProducts($event);
    }

    function it_delete_product_from_elasticsearch_index($indexer, RemoveEvent $event, ProductInterface $product)
    {
        $event->getSubject()->willReturn($product);
        $event->getSubjectId()->willReturn('40');

        $indexer->removeFromProductId('40')->shouldBeCalled();

        $this->deleteProduct($event)->shouldReturn(null);
    }

    function it_does_not_index_a_non_unitary_save_of_a_product(
        $indexer,
        GenericEvent $event,
        ProductInterface $product
    ) {
        $event->getSubject()->willReturn($product);
        $event->hasArgument('unitary')->willReturn(true);
        $event->getArgument('unitary')->willReturn(false);

        $indexer->indexFromProductIdentifier(Argument::any())->shouldNotBeCalled();

        $this->indexProduct($event);
    }

    function it_does_not_index_a_non_unitary_save_of_a_product_bis(
        $indexer,
        GenericEvent $event,
        ProductInterface $product
    ) {
        $event->getSubject()->willReturn($product);
        $event->hasArgument('unitary')->willReturn(false);

        $indexer->indexFromProductIdentifier(Argument::any())->shouldNotBeCalled();

        $this->indexProduct($event);
    }

    function it_does_not_index_a_non_product_entity($indexer)
    {
        $indexer->indexFromProductIdentifier(Argument::cetera())->shouldNotBeCalled();

        $this->indexProduct(new GenericEvent(new \stdClass()));
    }

    function it_does_not_bulk_index_non_product_entities(
        $indexer,
        GenericEvent $event,
        \stdClass $subject1
    ) {
        $event->getSubject()->willReturn([$subject1]);

        $indexer->indexFromProductIdentifiers(Argument::any())->shouldNotBeCalled();

        $this->bulkIndexProducts($event);
    }

    function it_does_not_bulk_index_non_collections($indexer)
    {
        $indexer->indexFromProductIdentifiers(Argument::any())->shouldNotBeCalled();

        $this->bulkIndexProducts(new GenericEvent(new \stdClass()));
    }

    function it_does_not_delete_non_product_entity_from_elasticsearch($indexer)
    {
        $indexer->removeFromProductId(Argument::any())->shouldNotBeCalled();

        $this->deleteProduct(new RemoveEvent(new \stdClass(), 40))->shouldReturn(null);
    }
}
