<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Log;

use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookRequest;
use Psr\Http\Message\ResponseInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventSubscriptionSendApiEventRequestLog
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

    public function getWebhookRequest(): WebhookRequest
    {
        return $this->webhookRequest;
    }

    /**
     * @return array<mixed>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getStartTime(): float
    {
        return $this->startTime;
    }

    public function setEndTime(float $endTime): void
    {
        $this->endTime = $endTime;
    }

    public function getEndTime(): ?float
    {
        return $this->endTime;
    }

    public function setResponse(?ResponseInterface $response): void
    {
        $this->response = $response;
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setSuccess(bool $success): void
    {
        $this->success = $success;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }
}
