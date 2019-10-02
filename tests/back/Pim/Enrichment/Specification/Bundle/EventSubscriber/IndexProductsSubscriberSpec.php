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
            StorageEvents::POST_REMOVE   => ['deleteProduct', 300],
        ]);
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

    function it_does_not_delete_non_product_entity_from_elasticsearch(
        ProductIndexerInterface $indexer
    ) {
        $indexer->removeFromProductId(Argument::any())->shouldNotBeCalled();

        $this->deleteProduct(new RemoveEvent(new \stdClass(), 40))->shouldReturn(null);
    }
}
