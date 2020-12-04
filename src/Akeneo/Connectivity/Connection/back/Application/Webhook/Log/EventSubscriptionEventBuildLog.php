<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Log;

use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use Akeneo\Platform\Component\EventQueue\EventInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventSubscriptionEventBuildLog
{
    const TYPE = 'event_api.event_build';

    private int $subscriptionCount;
    /** @var EventInterface|BulkEventInterface */
    private object $event;
    private float $startTime;
    private float $endTime;

    /**
     * @param EventInterface|BulkEventInterface $event
     */
    public function __construct(
        int $subscriptionCount,
        object $event,
        float $startTime,
        float $endTime
    ) {
        $this->subscriptionCount = $subscriptionCount;
        $this->event = $event;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
    }

    /**
     * @return array{
     *  type: string,
     *  subscription_count: int,
     *  event_count: int,
     *  duration: int,
     *  events: array<array{
     *      uuid: string,
     *      author: string,
     *      author_type: string,
     *      name: string,
     *      timestamp: int,
     *  }>
     * }
     */
    public function toLog(): array
    {
        $events = [];
        if ($this->event instanceof EventInterface) {
            $events[] = $this->event;
        }
        if ($this->event instanceof BulkEventInterface) {
            $events = $this->event->getEvents();
        }

        return [
            'type' => self::TYPE,
            'subscription_count' => $this->subscriptionCount,
            'event_count' => count($events),
            'duration' => $this->getDuration(),
            'events' => array_map(
                function (EventInterface $event) {
                    return [
                        'uuid' => $event->getUuid(),
                        'author' => $event->getAuthor()->name(),
                        'author_type' => $event->getAuthor()->type(),
                        'name' => $event->getName(),
                        'timestamp' => $event->getTimestamp(),
                    ];
                },
                $events
            ),
        ];
    }

    private function getDuration(): int
    {
        $duration = $this->endTime - $this->startTime;

        return (int) round($duration * 1000);
    }
}
