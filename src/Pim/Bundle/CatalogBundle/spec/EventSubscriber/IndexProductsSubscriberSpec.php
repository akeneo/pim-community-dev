<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Bundle\CatalogBundle\Elasticsearch\ProductIndexer;
use Pim\Bundle\CatalogBundle\EventSubscriber\IndexProductsSubscriber;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class IndexProductsSubscriberSpec extends ObjectBehavior
{
    function let(ProductIndexer $indexer)
    {
        $this->beConstructedWith($indexer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IndexProductsSubscriber::class);
    }

    function it_subscribe_to_the_save_events()
    {
        $events = $this->getSubscribedEvents();
        $events->shouldHaveCount(2);
        $events->shouldHaveKey(StorageEvents::POST_SAVE);
        $events->shouldHaveKey(StorageEvents::POST_SAVE_ALL);
    }

    function it_does_not_index_a_non_product_entity($indexer, GenericEvent $event, \stdClass $subject)
    {
        $event->getSubject()->willReturn($subject);
        $indexer->index(Argument::cetera())->shouldNotBeCalled();

        $this->indexProduct($event);
    }

    function it_does_not_index_a_non_unitary_save_of_a_product(
        $indexer,
        GenericEvent $event,
        ProductInterface $product
    ) {
        $event->getSubject()->willReturn($product);
        $event->hasArgument('unitary')->willReturn(true);
        $event->getArgument('unitary')->willReturn(false);

        $indexer->index(Argument::any())->shouldNotBeCalled();

        $this->indexProduct($event);
    }

    function it_does_not_index_a_non_unitary_save_of_a_product_bis(
        $indexer,
        GenericEvent $event,
        ProductInterface $product
    ) {
        $event->getSubject()->willReturn($product);
        $event->hasArgument('unitary')->willReturn(false);

        $indexer->index(Argument::any())->shouldNotBeCalled();

        $this->indexProduct($event);
    }

    function it_does_not_bulk_index_non_product_entities(
        $indexer,
        GenericEvent $event,
        \stdClass $subject1
    ) {
        $event->getSubject()->willReturn([$subject1]);

        $indexer->indexAll(Argument::any())->shouldNotBeCalled();

        $this->bulkIndexProducts($event);
    }

    function it_does_not_bulk_index_non_collections($indexer, GenericEvent $event, \stdClass $subject1)
    {
        $event->getSubject()->willReturn($subject1);

        $indexer->indexAll(Argument::any())->shouldNotBeCalled();

        $this->bulkIndexProducts($event);
    }

    function it_indexes_a_single_product($indexer, GenericEvent $event, ProductInterface $product)
    {
        $event->getSubject()->willReturn($product);
        $event->hasArgument('unitary')->willReturn(true);
        $event->getArgument('unitary')->willReturn(true);

        $product->getIdentifier()->willReturn('identifier');

        $indexer->index($product)->shouldBeCalled();

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

        $indexer->indexAll([$product1, $product2])->shouldBeCalled();

        $this->bulkIndexProducts($event);
    }
}
