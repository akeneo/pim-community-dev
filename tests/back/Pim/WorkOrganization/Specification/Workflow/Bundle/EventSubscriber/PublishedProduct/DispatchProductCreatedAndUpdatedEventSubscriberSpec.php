<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\PublishedProduct;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent\DispatchBufferedPimEventSubscriberInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\PublishedProduct\DispatchProductCreatedAndUpdatedEventSubscriber;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProduct;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\GenericEvent;

class DispatchProductCreatedAndUpdatedEventSubscriberSpec extends ObjectBehavior
{
    public function let(DispatchBufferedPimEventSubscriberInterface $baseDispatcher): void
    {
        $this->beConstructedWith($baseDispatcher);
    }

    public function it_is_an_event_subscriber(): void
    {
        $this->shouldHaveType(DispatchProductCreatedAndUpdatedEventSubscriber::class);
        $this->shouldImplement(DispatchBufferedPimEventSubscriberInterface::class);
    }

    public function it_subscribes_events(
        DispatchBufferedPimEventSubscriberInterface $baseDispatcher
    ): void {
        $this->getSubscribedEvents()->shouldReturn([
            StorageEvents::POST_SAVE => 'createAndDispatchPimEvents',
            StorageEvents::POST_SAVE_ALL => 'dispatchBufferedPimEvents',
        ]);
    }

    public function it_dedicates_creation_and_dispatch_to_the_base_dispatcher(
        DispatchBufferedPimEventSubscriberInterface $baseDispatcher
    ): void {
        $product = new Product();
        $genericEvent = new GenericEvent($product);
        $baseDispatcher->createAndDispatchPimEvents($genericEvent)->shouldBeCalled();

        $this->createAndDispatchPimEvents($genericEvent);
    }

    public function it_dedicates_dispatch_buffered_pim_events_to_the_base_dispatcher(
        DispatchBufferedPimEventSubscriberInterface $baseDispatcher
    ): void {

        $baseDispatcher->dispatchBufferedPimEvents()->shouldBeCalled();

        $this->dispatchBufferedPimEvents();
    }

    public function it_does_not_dedicates_creation_and_dispatch_to_the_base_dispatcher_if_the_subject_is_a_published_product(
        DispatchBufferedPimEventSubscriberInterface $baseDispatcher
    ): void {
        $published = new PublishedProduct();
        $genericEvent = new GenericEvent($published);
        $baseDispatcher->createAndDispatchPimEvents($genericEvent)->shouldNotBeCalled();

        $this->createAndDispatchPimEvents($genericEvent);
    }
}
