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
    /** @var int */
    private $webhookEventBuildCount;

    /** @var BusinessEvent */
    private $businessEvent;

    /** @var float */
    private $startTime;

    /** @var float */
    private $endTime;

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

    private function getDuration(): float
    {
        return $this->endTime - $this->startTime;
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
}
