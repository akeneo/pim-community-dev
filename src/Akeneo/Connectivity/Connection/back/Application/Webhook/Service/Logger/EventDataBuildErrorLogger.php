<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Service\Logger;

use Akeneo\Platform\Component\EventQueue\EventInterface;
use Psr\Log\LoggerInterface;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventDataBuildErrorLogger
{
    const TYPE = 'event_api.event_data_build_error';

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function log(string $message, string $connectionCode, int $userId, EventInterface $event): void
    {
        $log = [
            'type' => self::TYPE,
            'message' => $message,
            'connection_code' => $connectionCode,
            'user_id' => $userId,
            'event' => [
                'uuid' => $event->getUuid(),
                'author' => $event->getAuthor()->name(),
                'author_type' => $event->getAuthor()->type(),
                'name' => $event->getName(),
                'timestamp' => $event->getTimestamp(),
            ],
        ];

        $this->logger->warning(json_encode($log, JSON_THROW_ON_ERROR));
    }
}
