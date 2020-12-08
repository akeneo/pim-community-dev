<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\EventsApiRequestCounterInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service\EventsApiRequestCounter;
use PhpSpec\ObjectBehavior;

class EventsApiRequestCounterSpec extends ObjectBehavior
{
    public function it_is_an_events_api_request_counter(): void
    {
        $this->shouldHaveType(EventsApiRequestCounter::class);
        $this->shouldImplement(EventsApiRequestCounterInterface::class);
    }

    public function it_increment_count(): void
    {
        // TODO
    }
}
