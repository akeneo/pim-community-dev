<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Component\Webhook;

use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\Event;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use Akeneo\Platform\Component\Webhook\EventDataCollection;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class EventDataCollectionSpec extends ObjectBehavior
{
    public function it_is_an_event_data_collection(): void
    {
        $this->shouldBeAnInstanceOf(EventDataCollection::class);
    }

    public function it_holds_an_event_data(): void
    {
        $event = $this->createEvent();
        $data = ['data'];

        $this->setEventData($event, $data);

        $this->getEventData($event)->shouldReturn($data);
    }

    public function it_holds_an_event_data_error(): void
    {
        $event = $this->createEvent();
        $error = new \Exception();

        $this->setEventDataError($event, $error);

        $this->getEventData($event)->shouldReturn($error);
    }

    private function createEvent(): EventInterface
    {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $data = [];

        return new class ($author, $data) extends Event {
            public function getName(): string
            {
                return 'event_name';
            }
        };
    }
}
