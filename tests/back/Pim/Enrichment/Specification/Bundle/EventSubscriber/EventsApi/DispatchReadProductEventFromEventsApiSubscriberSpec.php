<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EventsApi;

use Akeneo\Connectivity\Connection\Domain\Webhook\Event\EventsApiRequestSucceededEvent;
use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EventsApi\DispatchReadProductEventFromEventsApiSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Event\Connector\ReadProductsEvent;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Platform\Component\EventQueue\Event;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DispatchReadProductEventFromEventsApiSubscriberSpec extends ObjectBehavior
{
    public function let(EventDispatcherInterface $eventDispatcher)
    {
        $this->beConstructedWith($eventDispatcher);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(DispatchReadProductEventFromEventsApiSubscriber::class);
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_dispatches_a_read_product_on_product_events_api_saved(
        EventDispatcherInterface $eventDispatcher,
        EventsApiRequestSucceededEvent $eventsApiRequestSucceeded,
        ProductCreated $productCreatedEvent,
        ProductUpdated $productUpdatedEvent,
        ProductRemoved $productRemovedEvent
    ) {
        $eventsApiRequestSucceeded->getEvents()
            ->willReturn([$productCreatedEvent, $productUpdatedEvent, $productRemovedEvent]);

        $eventsApiRequestSucceeded->getConnectionCode()->willReturn('code');
        $eventDispatcher->dispatch(Argument::that(
            function (ReadProductsEvent $event) {
                return 3 === $event->getCount()
                    && 'code' === $event->getConnectionCode();
            })
        )->shouldbeCalledTimes(1);

        $this->dispatchReadProductOnProductEventsApiSaved($eventsApiRequestSucceeded);
    }

    public function it_doesnt_dispatch_a_read_product_on_product_events_api_saved_if_no_product_saved_event_type(
        EventDispatcherInterface $eventDispatcher,
        EventsApiRequestSucceededEvent $eventsApiRequestSucceeded,
        Event $unknownEvent
    ) {
        $eventsApiRequestSucceeded->getEvents()->willReturn([$unknownEvent]);
        $eventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();

        $this->dispatchReadProductOnProductEventsApiSaved($eventsApiRequestSucceeded);
    }
}
