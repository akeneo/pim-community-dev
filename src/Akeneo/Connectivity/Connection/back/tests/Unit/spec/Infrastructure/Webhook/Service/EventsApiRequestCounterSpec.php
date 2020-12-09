<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\EventsApiRequestCounterInterface;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Repository\EventsApiRequestCountRepository;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service\EventsApiRequestCounter;
use PhpSpec\ObjectBehavior;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventsApiRequestCounterSpec extends ObjectBehavior
{
    public function let(EventsApiRequestCountRepository $eventsApiRequestCountRepository): void
    {
        $this->beConstructedWith($eventsApiRequestCountRepository);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(EventsApiRequestCounter::class);
        $this->shouldImplement(EventsApiRequestCounterInterface::class);
    }

    public function it_increments_event_api_requests_count($eventsApiRequestCountRepository): void
    {
        $aDateTime = new \DateTimeImmutable('2020-12-09T14:28:00+00:00', new \DateTimeZone('UTC'));
        $anEventCount = 42;
        $aNumberOfUpdatedLines = 3;

        $eventsApiRequestCountRepository->upsert($aDateTime, $anEventCount)->willReturn($aNumberOfUpdatedLines);

        $this->incrementCount($aDateTime, $anEventCount);
    }
}

