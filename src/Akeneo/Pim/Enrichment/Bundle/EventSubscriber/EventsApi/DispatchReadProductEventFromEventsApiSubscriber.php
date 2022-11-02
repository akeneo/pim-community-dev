<?php

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EventsApi;

use Akeneo\Connectivity\Connection\Domain\Webhook\Event\EventsApiRequestSucceededEvent;
use Akeneo\Pim\Enrichment\Component\Product\Event\Connector\ReadProductsEvent;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DispatchReadProductEventFromEventsApiSubscriber implements EventSubscriberInterface
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [EventsApiRequestSucceededEvent::class => 'dispatchReadProductOnProductEventsApiSaved'];
    }

    public function dispatchReadProductOnProductEventsApiSaved(
        EventsApiRequestSucceededEvent $eventsApiRequestSucceeded
    ) {
        $count = 0;
        foreach ($eventsApiRequestSucceeded->getEvents() as $event) {
            if ($event instanceof ProductUpdated
                || $event instanceof ProductCreated
                || $event instanceof ProductRemoved) {
                $count++;
            }
        }
        if ($count === 0) {
            return;
        }
        $this->eventDispatcher->dispatch(new ReadProductsEvent(
            $count,
            $eventsApiRequestSucceeded->getConnectionCode()
        ));
    }
}
