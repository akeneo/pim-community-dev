<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Service;

use Akeneo\Connectivity\Connection\Domain\Clock;
use Akeneo\Connectivity\Connection\Domain\Webhook\EventNormalizer\EventNormalizer;
use Akeneo\Connectivity\Connection\Domain\Webhook\EventNormalizer\EventNormalizerInterface;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\EventsApiDebugLogLevels;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Repository\EventsApiDebugRepository;
use Akeneo\Platform\Component\EventQueue\EventInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventsApiDebugLogger implements EventsApiDebugSendRequestErrorLogger
{
    private Clock $clock;

    private EventsApiDebugRepository $repository;

    private EventNormalizerInterface $defaultEventNormalizer;

    /** @var iterable<EventNormalizerInterface> */
    private iterable $eventNormalizers;

    /**
     * @param iterable<EventNormalizerInterface> $eventNormalizers
     */
    public function __construct(
        EventsApiDebugRepository $repository,
        Clock $clock,
        iterable $eventNormalizers
    ) {
        $this->repository = $repository;
        $this->clock = $clock;
        $this->defaultEventNormalizer = new EventNormalizer();
        $this->eventNormalizers = $eventNormalizers;
    }

    public function logEventSubscriptionSkippedOwnEvent(
        string $connectionCode,
        EventInterface $event
    ): void {
        $this->repository->persist([
            'timestamp' => $this->clock->now()->getTimestamp(),
            'level' => EventsApiDebugLogLevels::NOTICE,
            'message' => 'The event was not sent because it was raised by the same connection.',
            'connection_code' => $connectionCode,
            'context' => [
                'event' => $this->normalizeEvent($event)
            ]
        ]);
    }

    public function logLimitOfEventsApiRequestsReached(): void
    {
        $this->repository->persist([
            'timestamp' => $this->clock->now()->getTimestamp(),
            'level' => EventsApiDebugLogLevels::WARNING,
            'message' => 'The maximum number of events sent per hour has been reached.',
            'connection_code' => null,
            'context' => [],
        ]);
    }

    /**
     * @param string $connectionCode
     * @param array<EventInterface> $events
     * @param string $url
     * @param int $statusCode
     * @param array<string> $headers
     */
    public function logSendRequestError(string $connectionCode, array $events, string $url, int $statusCode, array $headers): void
    {
        $this->addLog([
            'timestamp' => $this->clock->now()->getTimestamp(),
            'level' => EventsApiDebugLogLevels::ERROR,
            'message' => 'The endpoint returned an error.',
            'connection_code' => $connectionCode,
            'event_subscription_url' => $url,
            'status_code' => $statusCode,
            'headers' => $headers,
            'context' => [
                'events' => array_map(function ($event) {
                    $this->normalizeEvent($event);
                }, $events),
            ]
        ]);
    }

    public function flushLogs(): void
    {
        if (0 === count($this->buffer)) {
            return;
        }

        $this->repository->bulkInsert($this->buffer);
        $this->buffer = [];
    }

    /**
     * @param array{
     *  timestamp: int,
     *  level: string,
     *  message: string,
     *  connection_code: ?string,
     *  context: array
     * } $log
     */
    private function addLog(array $log): void
    {
        $this->buffer[] = $log;

        if (count($this->buffer) >= $this->bufferSize) {
            $this->flushLogs();
        }
    }

    /**
     * @return array<mixed>
     */
    private function normalizeEvent(EventInterface $event): array
    {
        foreach ($this->eventNormalizers as $normalizer) {
            if (true === $normalizer->supports($event)) {
                return $normalizer->normalize($event);
            }
        }

        return $this->defaultEventNormalizer->normalize($event);
    }
}
