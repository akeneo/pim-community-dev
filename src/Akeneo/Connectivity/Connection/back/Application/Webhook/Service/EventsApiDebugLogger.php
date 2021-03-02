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
class EventsApiDebugLogger
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
