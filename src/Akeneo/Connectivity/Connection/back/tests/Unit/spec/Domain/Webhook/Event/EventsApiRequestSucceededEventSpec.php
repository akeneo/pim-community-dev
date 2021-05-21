<?php

namespace spec\Akeneo\Connectivity\Connection\Domain\Webhook\Event;

use Akeneo\Connectivity\Connection\Domain\Webhook\Event\EventsApiRequestSucceededEvent;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventsApiRequestSucceededEventSpec extends ObjectBehavior
{
    public function let(EventInterface $event): void
    {
        $this->beConstructedWith('connectionCode', [$event]);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(EventsApiRequestSucceededEvent::class);
    }

    public function it_provides_the_events(EventInterface $event)
    {
        $this->getEvents()->shouldBe([$event]);
    }

    public function it_provides_the_connection_code()
    {
        $this->getConnectionCode()->shouldBe('connectionCode');
    }
}
