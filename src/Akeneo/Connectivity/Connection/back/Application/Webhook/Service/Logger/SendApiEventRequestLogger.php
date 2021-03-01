<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Service\Logger;

use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookRequest;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SendApiEventRequestLogger
{
    const TYPE = 'event_api.send_api_event_request';

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param array<mixed> $headers
     */
    public function log(
        WebhookRequest $webhookRequest,
        float $startTime,
        float $endTime,
        array $headers,
        string $message,
        bool $success,
        ?ResponseInterface $response
    ): void {
        $log = [
            'type' => self::TYPE,
            'duration_ms' => $this->getDurationMs($startTime, $endTime),
            'headers' => $headers,
            'message' => $message,
            'success' => $success,
            'response' => $response ? ['status_code' => $response->getStatusCode()] : null,
            'events' => array_map(function (WebhookEvent $event) {
                $date = \DateTime::createFromFormat(\DateTime::ATOM, $event->eventDateTime());
                return [
                    'uuid' => $event->eventId(),
                    'author' => $event->author()->name(),
                    'author_type' => $event->author()->type(),
                    'name' => $event->action(),
                    'timestamp' => $date ? $date->getTimestamp() : null,
                ];
            }, $webhookRequest->apiEvents()),
        ] + $this->getPropagationSeconds($webhookRequest, $endTime);

        $this->logger->info(json_encode($log, JSON_THROW_ON_ERROR));
    }

    /**
     * @return array{
     *  max_propagation_seconds?: int,
     *  min_propagation_seconds?: int,
     * }
     */
    private function getPropagationSeconds(WebhookRequest $webhookRequest, float $endTime): array
    {
        $youngerEventTimestamp = null;
        $olderEventTimestamp = null;

        foreach ($webhookRequest->apiEvents() as $event) {
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
            'max_propagation_seconds' => (int) $endTime - $olderEventTimestamp,
            'min_propagation_seconds' => (int) $endTime - $youngerEventTimestamp,
        ] : [];
    }

    private function getDurationMs(float $startTime, float $endTime): int
    {
        $durationSeconds = $endTime - $startTime;

        return (int) round($durationSeconds * 1000);
    }
}
