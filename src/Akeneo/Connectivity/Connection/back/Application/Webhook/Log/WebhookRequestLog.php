<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Log;

use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookRequest;
use Psr\Http\Message\ResponseInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebhookRequestLog
{
    private WebhookRequest $webhookRequest;
    private float $startTime;
    private ?float $endTime = null;
    private ?ResponseInterface $response;

    public function __construct(
        WebhookRequest $webhookRequest,
        float $startTime
    ) {
        $this->webhookRequest = $webhookRequest;
        $this->startTime = $startTime;
    }

    public function setEndTime(float $endTime): void
    {
        $this->endTime = $endTime;
    }

    public function setResponse(?ResponseInterface $response): void
    {
        $this->response = $response;
    }

    /**
     * @return array{
     *  type: string,
 *      duration: string,
     *  response: array{status_code: int}|null,
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
        $date = \DateTime::createFromFormat(\DateTime::ATOM, $this->webhookRequest->event()->eventDate());

        return [
            'type' => 'webhook.send_request',
            'duration' => (string) $this->getDuration(),
            'response' => $this->response ? ['status_code' => $this->response->getStatusCode()] : null,
            'event' => [
                'uuid' => $this->webhookRequest->event()->eventId(),
                'author' => $this->webhookRequest->event()->author(),
                'name' => $this->webhookRequest->event()->action(),
                'timestamp' => ($date) ? $date->getTimestamp() : null,
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
