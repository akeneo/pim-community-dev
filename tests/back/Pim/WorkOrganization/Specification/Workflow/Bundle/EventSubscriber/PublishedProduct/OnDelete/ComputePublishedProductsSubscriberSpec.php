<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\PublishedProduct\OnDelete;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Indexer\PublishedProductIndexer;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\PublishedProduct\OnDelete\ComputePublishedProductsSubscriber;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProduct;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ComputePublishedProductsSubscriberSpec extends ObjectBehavior
{
    function let(PublishedProductIndexer $publishedProductIndexer)
    {
        $this->beConstructedWith($publishedProductIndexer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ComputePublishedProductsSubscriber::class);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_subscribes_to_post_remove_events()
    {
        $this::getSubscribedEvents()->shouldHaveKey(StorageEvents::POST_REMOVE);
    }

    function it_only_handles_published_products(PublishedProductIndexer $publishedProductIndexer)
    {
        $publishedProductIndexer->remove(Argument::any())->shouldNotBeCalled();
        $this->deletePublishedProduct(new RemoveEvent(new \stdClass(), 42));
    }

    function it_deletes_a_published_product_from_the_index(PublishedProductIndexer $publishedProductIndexer)
    {
        $publishedProductIndexer->remove(42)->shouldBeCalled();
        $this->deletePublishedProduct(new RemoveEvent(new PublishedProduct(), 42));
    }
}
