<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Log;

use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use Akeneo\Platform\Component\EventQueue\EventInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventSubscriptionDataBuildErrorLog
{
    const TYPE = 'event_api.event_data_build_error';

    private string $message;
    private string $connectionCode;
    private int $userId;

    /** @var EventInterface|BulkEventInterface */
    private object $event;

    /**
     * @param EventInterface|BulkEventInterface $event
     */
    public function __construct(
        string $message,
        string $connectionCode,
        int $userId,
        object $event
    ) {
        $this->message = $message;
        $this->connectionCode = $connectionCode;
        $this->userId = $userId;
        $this->event = $event;
    }

    /**
     * @return array{
     *  type: string,
     *  message: string,
     *  connection_code: string,
     *  user_id: int,
     *  events: array<array{
     *      uuid: string,
     *      author: string,
     *      author_type: string,
     *      name: string,
     *      timestamp: int,
     *  }>,
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
            'message' => $this->message,
            'connection_code' => $this->connectionCode,
            'user_id' => $this->userId,
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
}
