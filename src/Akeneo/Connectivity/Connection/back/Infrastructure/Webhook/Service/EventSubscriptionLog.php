<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service;

use Akeneo\Connectivity\Connection\Application\Webhook\Log\EventSubscriptionSendApiEventRequestLog;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\EventSubscriptionLogInterface;
use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use Psr\Log\LoggerInterface;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventSubscriptionLog implements EventSubscriptionLogInterface
{
    const TYPE_EVENT_DATA_BUILD_ERROR = 'event_api.event_data_build_error';
    const TYPE_EVENT_BUILD = 'event_api.event_build';
    const TYPE_REACH_REQUEST_LIMIT = 'event_api.reach_requests_limit';
    const TYPE_SKIP_OWN_EVENT = 'event_api.skip_own_event';
    const TYPE_SEND_API_EVENT_REQUEST = 'event_api.send_api_event_request';

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function logEventDataBuildError(string $message, string $connectionCode, int $userId, object $event): void
    {
        $events = [];
        if ($event instanceof EventInterface) {
            $events[] = $event;
        }
        if ($event instanceof BulkEventInterface) {
            $events = $event->getEvents();
        }

        $log = [
            'type' => self::TYPE_EVENT_DATA_BUILD_ERROR,
            'message' => $message,
            'connection_code' => $connectionCode,
            'user_id' => $userId,
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

        $this->logger->warning(json_encode($log, JSON_THROW_ON_ERROR));
    }

    public function logEventBuild(int $subscriptionCount, int $durationMs, int $eventBuiltCount, object $event): void
    {
        $events = [];
        if ($event instanceof EventInterface) {
            $events[] = $event;
        }
        if ($event instanceof BulkEventInterface) {
            $events = $event->getEvents();
        }

        $log = [
            'type' => self::TYPE_EVENT_BUILD,
            'subscription_count' => $subscriptionCount,
            'event_count' => count($events),
            'event_built_count' => $eventBuiltCount,
            'duration_ms' => $durationMs,
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

        $this->logger->info(json_encode($log, JSON_THROW_ON_ERROR));
    }

    public function logReachRequestLimit(int $limit, \DateTimeImmutable $reachedLimitDateTime, int $delayUntilNextRequest): void
    {
        $log = [
            'type' => self::TYPE_REACH_REQUEST_LIMIT,
            'message' => 'event subscription requests limit has been reached',
            'limit' => $limit,
            'retry_after_seconds' => $delayUntilNextRequest,
            'limit_reset' => $reachedLimitDateTime
                ->add(new \DateInterval('PT' . $delayUntilNextRequest . 'S'))
                ->format(\DateTimeInterface::ATOM)
        ];

        $this->logger->info(json_encode($log, JSON_THROW_ON_ERROR));
    }

    public function logSkipOwnEvent(EventInterface $event, string $connectionCode): void
    {
        $log = [
            'type' => self::TYPE_SKIP_OWN_EVENT,
            'connection_code' => $connectionCode,
            'event' => [
                'uuid' => $event->getUuid(),
                'author' => $event->getAuthor()->name(),
                'author_type' => $event->getAuthor()->type(),
                'name' => $event->getName(),
                'timestamp' => $event->getTimestamp(),
            ],
        ];

        $this->logger->info(json_encode($log, JSON_THROW_ON_ERROR));
    }

    public function logSendApiEventRequest(EventSubscriptionSendApiEventRequestLog $eventSubscriptionSendApiEventRequestLog): void
    {
        $this->logger->info(json_encode($eventSubscriptionSendApiEventRequestLog->toLog(), JSON_THROW_ON_ERROR));
    }
}
