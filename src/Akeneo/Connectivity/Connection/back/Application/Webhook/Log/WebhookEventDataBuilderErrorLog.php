<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Log;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ActiveWebhook;
use Akeneo\Platform\Component\EventQueue\BusinessEventInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebhookEventDataBuilderErrorLog
{
    private string $message;
    private ActiveWebhook $webhook;
    private BusinessEventInterface $businessEvent;

    public function __construct(
        string $message,
        ActiveWebhook $webhook,
        BusinessEventInterface $businessEvent
    ) {
        $this->message = $message;
        $this->webhook = $webhook;
        $this->businessEvent = $businessEvent;
    }

    /**
     * @return array{
     *  type: string,
     *  message: string,
     *  webhook: array{
            connection_code: string,
     *      user_id: int,
     *  },
     *  event: array{
     *      uuid: string,
     *      author: string,
     *      name: string,
     *      timestamp: int|null,
     *  },
     * }
     */
    public function toLog(): array
    {
        return [
            'type' => 'webhook.event_build',
            'message' => $this->message,
            'webhook' => [
                'connection_code' => $this->webhook->connectionCode(),
                'user_id' => $this->webhook->userId(),
            ],
            'event' => [
                'uuid' => $this->businessEvent->uuid(),
                'author' => $this->businessEvent->author(),
                'name' => $this->businessEvent->name(),
                'timestamp' => $this->businessEvent->timestamp(),
            ],
        ];
    }
}
