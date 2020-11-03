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
    /** @var array<string, int|string> */
    private array $headers;
    private string $message = '';
    private bool $success;
    private float $startTime;
    private ?float $endTime = null;
    private ?ResponseInterface $response;

    /**
     * @param array<string, int|string> $headers
     */
    public function __construct(
        WebhookRequest $webhookRequest,
        array $headers,
        float $startTime
    ) {
        $this->webhookRequest = $webhookRequest;
        $this->headers = $headers;
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

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function setSuccess(bool $success): void
    {
        $this->success = $success;
    }

    /**
     * @return array{
     *  type: string,
     *  duration: int,
     *  headers: array<string, int|string>,
     *  message: string,
     *  success: bool,
     *  response: array{status_code: int}|null,
     *  event: array{
     *      uuid: string,
     *      author: string,
     *      author_type: string,
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
            'duration' => $this->getDuration(),
            'headers' => $this->headers,
            'message' => $this->message,
            'success' => $this->success,
            'response' => $this->response ? ['status_code' => $this->response->getStatusCode()] : null,
            'event' => [
                'uuid' => $this->webhookRequest->event()->eventId(),
                'author' => $this->webhookRequest->event()->author()->name(),
                'author_type' => $this->webhookRequest->event()->author()->type(),
                'name' => $this->webhookRequest->event()->action(),
                'timestamp' => ($date) ? $date->getTimestamp() : null,
            ],
        ];
    }

    private function getDuration(): int
    {
        if (null === $this->endTime) {
            throw new \RuntimeException();
        }

        $duration = $this->endTime - $this->startTime;

        return (int) round($duration * 1000);
    }
}
