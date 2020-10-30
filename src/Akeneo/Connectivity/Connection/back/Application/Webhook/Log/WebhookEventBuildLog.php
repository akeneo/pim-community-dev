<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Log;

use Akeneo\Platform\Component\EventQueue\BusinessEvent;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebhookEventBuildLog
{
    private int $webhookEventBuildCount;
    private BusinessEvent $businessEvent;
    private float $startTime;
    private float $endTime;

    public function __construct(
        int $webhookEventBuildCount,
        BusinessEvent $businessEvent,
        float $startTime,
        float $endTime
    ) {
        $this->webhookEventBuildCount = $webhookEventBuildCount;
        $this->businessEvent = $businessEvent;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
    }

    public function toLog(): array
    {
        return [
            'type' => 'webhook.business_event_build',
            'monitor' => [
                'webhook_event_build_count' => $this->webhookEventBuildCount,
                'duration' => (string) $this->getDuration(),
            ],
            'business_event' => [
                'uuid' => $this->businessEvent->uuid(),
                'author' => $this->businessEvent->author(),
                'name' => $this->businessEvent->name(),
                'timestamp' => $this->businessEvent->timestamp(),
            ],
        ];
    }

    private function getDuration(): float
    {
        if (null === $this->endTime) {
            throw new \RuntimeException();
        }

        $duration = $this->endTime - $this->startTime;

        return round($duration * 1000);
    }
}
