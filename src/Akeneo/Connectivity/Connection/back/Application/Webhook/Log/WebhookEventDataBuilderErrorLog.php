<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Log;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ActiveWebhook;
use Akeneo\Platform\Component\EventQueue\BusinessEvent;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebhookEventDataBuilderErrorLog
{
    /** @var string */
    private $message;

    /** @var ActiveWebhook */
    private $webhook;

    /** @var BusinessEvent */
    private $businessEvent;

    public function __construct(
        string $message,
        ActiveWebhook $webhook,
        BusinessEvent $businessEvent
    ) {
        $this->message = $message;
        $this->webhook = $webhook;
        $this->businessEvent = $businessEvent;
    }

    public function toLog(): array
    {
        return [
            'type' => 'webhook.business_event_build',
            'message' => $this->message,
            'webhook' => [
                'connection_code' => $this->webhook->connectionCode(),
                'user_id' => $this->webhook->userId(),
            ],
            'business_event' => [
                'uuid' => $this->businessEvent->uuid(),
                'author' => $this->businessEvent->author(),
                'name' => $this->businessEvent->name(),
                'timestamp' => $this->businessEvent->timestamp(),
            ],
        ];
    }
}
