<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Component\EventQueue;

use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use Akeneo\Platform\Component\EventQueue\Event;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class BulkEventSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith([]);
    }

    public function it_is_a_bulk_event(): void
    {
        $this->shouldBeAnInstanceOf(BulkEvent::class);
        $this->shouldImplement(BulkEventInterface::class);
    }

    public function it_returns_the_events(): void
    {
        $events = [
            $this->createEvent(),
            $this->createEvent(),
        ];
        $this->beConstructedWith($events);

        $this->getEvents()->shouldReturn($events);
    }

    public function it_validates_the_events(): void
    {
        $events = [
            $this->createEvent(),
            new \stdClass(),
        ];
        $this->beConstructedWith($events);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    private function createEvent(): EventInterface
    {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $data = [];

        return new class($author, $data) extends Event {
            public function getName(): string
            {
                return 'event_name';
            }
        };
    }
}
