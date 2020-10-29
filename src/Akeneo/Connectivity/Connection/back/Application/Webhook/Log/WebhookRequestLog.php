<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Log;

use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookRequest;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebhookRequestLog
{
    /** @var WebhookRequest */
    private $webhookRequest;

    /** @var float */
    private $startTime;

    /** @var float */
    private $endTime;

    public function __construct(
        WebhookRequest $webhookRequest,
        float $startTime,
        float $endTime = null
    ) {
        $this->webhookRequest = $webhookRequest;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
    }

    public function setEndTime($endTime): float
    {
        $this->endTime = $endTime;
    }

    public function toLog(): array
    {
        $date = \DateTime::createFromFormat(\DateTime::ATOM, $this->webhookRequest->event()->eventDate());

        return [
            'type' => 'webhook.send_request',
            'monitor' => [
                'duration' => (string) $this->getDuration(),
            ],
            'business_event' => [
                'uuid' => $this->webhookRequest->event()->eventId(),
                'author' => $this->webhookRequest->event()->author(),
                'name' => $this->webhookRequest->event()->action(),
                'timestamp' => $date->getTimestamp(),
            ],
        ];
    }

    private function getDuration(): float
    {
        return $this->endTime - $this->startTime;
    }
}
