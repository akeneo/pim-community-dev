<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Log;

use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookRequest;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Psr\Http\Message\ResponseInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventSubscriptionSendApiEventRequestLog
{
    const TYPE = 'event_api.send_api_event_request';

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
     *  duration_ms: int,
     *  headers: array<string, int|string>,
     *  message: string,
     *  success: bool,
     *  response: array{status_code: int}|null,
     *  events: array<array{
     *      uuid: string,
     *      author: string,
     *      author_type: string,
     *      name: string,
     *      timestamp: int|null,
     *  }>,
     *  max_propagation_seconds?: int,
     *  min_propagation_seconds?: int,
     * }
     */
    public function toLog(): array
    {
        return [
            'type' => self::TYPE,
            'duration_ms' => $this->getDurationMs(),
            'headers' => $this->headers,
            'message' => $this->message,
            'success' => $this->success,
            'response' => $this->response ? ['status_code' => $this->response->getStatusCode()] : null,
            'events' => array_map(function (WebhookEvent $event) {
                $date = \DateTime::createFromFormat(\DateTime::ATOM, $event->eventDateTime());
                return [
                    'uuid' => $event->eventId(),
                    'author' => $event->author()->name(),
                    'author_type' => $event->author()->type(),
                    'name' => $event->action(),
                    'timestamp' => $date ? $date->getTimestamp() : null,
                ];
            }, $this->webhookRequest->apiEvents()),
        ] + $this->getPropagationSeconds();
    }

    /**
     * @return array{
     *  max_propagation_seconds?: int,
     *  min_propagation_seconds?: int,
     * }
     */
    private function getPropagationSeconds(): array
    {
        $youngerEventTimestamp = null;
        $olderEventTimestamp = null;
        foreach ($this->webhookRequest->apiEvents() as $event) {
            $date = \DateTimeImmutable::createFromFormat(\DateTime::ATOM, $event->eventDateTime());
            $timestamp = $date ? $date->getTimestamp() : null;

            if (null === $youngerEventTimestamp) {
                $youngerEventTimestamp = $timestamp;
            }
            if (null !== $timestamp) {
                $youngerEventTimestamp = max($timestamp, $youngerEventTimestamp);
            }

            if (null === $olderEventTimestamp) {
                $olderEventTimestamp = $timestamp;
            }
            if (null !== $timestamp) {
                $olderEventTimestamp = min($timestamp, $olderEventTimestamp);
            }
        }
        
        return null !== $olderEventTimestamp && null !== $youngerEventTimestamp ? [
            'max_propagation_seconds' => (int) $this->endTime - $olderEventTimestamp,
            'min_propagation_seconds' => (int) $this->endTime - $youngerEventTimestamp,
        ] : [];
    }
    
    private function getDurationMs(): int
    {
        if (null === $this->endTime) {
            throw new \RuntimeException();
        }

        $duration_seconds = $this->endTime - $this->startTime;

        return (int) round($duration_seconds * 1000);
    }
}
