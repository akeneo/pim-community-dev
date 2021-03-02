<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Service\Logger;

use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use Psr\Log\LoggerInterface;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventBuildLogger
{
    const TYPE = 'event_api.event_build';

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    public function log(int $subscriptionCount, int $durationMs, int $eventBuiltCount, BulkEventInterface $events): void
    {
        $log = [
            'type' => self::TYPE,
            'subscription_count' => $subscriptionCount,
            'event_count' => count($events->getEvents()),
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
                $events->getEvents()
            ),
        ];

        $this->logger->info(json_encode($log, JSON_THROW_ON_ERROR));
    }
}
