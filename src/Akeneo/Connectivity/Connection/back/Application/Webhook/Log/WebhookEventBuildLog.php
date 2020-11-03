<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Log;

use Akeneo\Platform\Component\EventQueue\BusinessEventInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebhookEventBuildLog
{
    private int $webhookEventBuildCount;
    private BusinessEventInterface $businessEvent;
    private float $startTime;
    private float $endTime;

    public function __construct(
        int $webhookEventBuildCount,
        BusinessEventInterface $businessEvent,
        float $startTime,
        float $endTime
    ) {
        $this->webhookEventBuildCount = $webhookEventBuildCount;
        $this->businessEvent = $businessEvent;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
    }

    /**
     * @return array{
     *  type: string,
 *      webhook_event_build_count: int,
 *      duration: string,
     *  event: array{
     *      uuid: string,
     *      author: string,
     *      name: string,
     *      timestamp: int,
     *  }
     * }
     */
    public function toLog(): array
    {
        return [
            'type' => 'webhook.event_build',
            'webhook_event_build_count' => $this->webhookEventBuildCount,
            'duration' => (string) $this->getDuration(),
            'event' => [
                'uuid' => $this->businessEvent->uuid(),
                'author' => $this->businessEvent->author(),
                'name' => $this->businessEvent->name(),
                'timestamp' => $this->businessEvent->timestamp(),
            ],
        ];
    }

    private function getDuration(): float
    {
        $duration = $this->endTime - $this->startTime;

        return round($duration * 1000);
    }
}
