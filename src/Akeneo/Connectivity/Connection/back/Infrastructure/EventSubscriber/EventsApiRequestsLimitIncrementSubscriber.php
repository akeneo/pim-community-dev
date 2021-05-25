<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\EventSubscriber;

use Akeneo\Connectivity\Connection\Domain\Webhook\Event\EventsApiRequestFailedEvent;
use Akeneo\Connectivity\Connection\Domain\Webhook\Event\EventsApiRequestSucceededEvent;
use Akeneo\Connectivity\Connection\Domain\Webhook\Event\MessageProcessedEvent;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Repository\EventsApiRequestCountRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class EventsApiRequestsLimitIncrementSubscriber implements EventSubscriberInterface
{
    private EventsApiRequestCountRepository $repository;
    private int $count;

    public function __construct(EventsApiRequestCountRepository $repository)
    {
        $this->repository = $repository;
        $this->count = 0;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EventsApiRequestSucceededEvent::class => 'incrementRequestCount',
            EventsApiRequestFailedEvent::class => 'incrementRequestCount',
            MessageProcessedEvent::class => 'saveRequestCount',
        ];
    }

    public function incrementRequestCount(): int
    {
        return ++$this->count;
    }

    public function saveRequestCount(): void
    {
        if (0 === $this->count) {
            return;
        }

        $this->repository->upsert(new \DateTimeImmutable('now', new \DateTimeZone('UTC')), $this->count);
        $this->count = 0;
    }
}
