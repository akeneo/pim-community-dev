<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EventsApi;

use Akeneo\Connectivity\Connection\Domain\Webhook\Event\EventsApiRequestSucceeded;
use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EventsApi\DispatchReadProductEventFromEventsApiSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Event\Connector\ReadProductsEvent;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DispatchReadProductEventFromEventsApiSubscriberSpec extends ObjectBehavior
{
    public function let(EventDispatcher $eventDispatcher)
    {
        $this->beConstructedWith($eventDispatcher);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(DispatchReadProductEventFromEventsApiSubscriber::class);
    }

    public function it_dispatches_a_read_product_on_product_event_api_saved(
        EventDispatcher $eventDispatcher,
        EventsApiRequestSucceeded $eventsApiRequestSucceeded,
        ProductUpdated $productUpdatedEvent,
        ProductCreated $productCreatedEvent,
        ProductRemoved $productRemovedEvent
    ) {
        $eventsApiRequestSucceeded->getEvents()
            ->willReturn([$productUpdatedEvent, $productCreatedEvent, $productRemovedEvent])
            ->shouldBeCalledTimes(1);

        $eventsApiRequestSucceeded->getConnectionCode()->willReturn('code')->shouldBeCalledTimes(1);
        $eventDispatcher->dispatch(Argument::allOf(
            Argument::type(ReadProductsEvent::class),
            Argument::that(function ($event) {
                if(!$event instanceof ReadProductsEvent) {
                    return false;
                }
                if($event->getCount() !== 2) {
                    return false;
                }
                if($event->getConnectionCode() !== 'code') {
                    return false;
                }
                if($event->isEventApi() !== true) {
                    return false;
                }
                
                return true;
            })
        ))->shouldbeCalledTimes(1);

        $this->dispatchReadProductOnProductEventApiSaved($eventsApiRequestSucceeded);
    }

    public function it_doesnt_dispatch_a_read_product_on_product_event_api_saved_if_no_product_saved_event_type(
        EventDispatcher $eventDispatcher,
        EventsApiRequestSucceeded $eventsApiRequestSucceeded,
        ProductRemoved $productRemovedEvent
    ) {
        $eventsApiRequestSucceeded->getEvents()
            ->willReturn([$productRemovedEvent])
            ->shouldBeCalledTimes(1);

        $eventsApiRequestSucceeded->getConnectionCode()->willReturn('code')->shouldBeCalledTimes(1);
        $eventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();

        $this->dispatchReadProductOnProductEventApiSaved($eventsApiRequestSucceeded);
    }
}
