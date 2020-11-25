<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Log;

use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use Akeneo\Platform\Component\EventQueue\EventInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebhookEventDataBuilderErrorLog
{
    private string $message;
    private string $webhookConnectionCode;

    /** @var EventInterface|BulkEventInterface */
    private object $event;

    /**
     * @param EventInterface|BulkEventInterface $event
     */
    public function __construct(
        string $message,
        string $webhookConnectionCode,
        object $event
    ) {
        $this->message = $message;
        $this->webhookConnectionCode = $webhookConnectionCode;
        $this->event = $event;
    }

    /**
     * @return array{
     *  type: string,
     *  message: string,
     *  webhook_connection_code: string,
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
            'type' => 'webhook.event_build',
            'message' => $this->message,
            'webhook_connection_code' => $this->webhookConnectionCode,
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
