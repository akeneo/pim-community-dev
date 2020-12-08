<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\EventsApiRequestCounterInterface;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Repository\EventsApiRequestCountRepository;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventsApiRequestCounter implements EventsApiRequestCounterInterface
{
    private EventsApiRequestCountRepository $eventsApiRequestCountRepository;

    public function __construct(
        EventsApiRequestCountRepository $eventsApiRequestCountRepository,
    ) {
        $this->eventsApiRequestCountRepository = $eventsApiRequestCountRepository;
    }

    public function incrementCount(\DateTimeImmutable $dateTime, int $eventCount): void
    {
        $this->eventsApiRequestCountRepository->upsert($dateTime, $eventCount);
    }
}
