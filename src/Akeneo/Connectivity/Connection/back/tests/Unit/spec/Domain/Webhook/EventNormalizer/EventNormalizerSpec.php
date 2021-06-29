<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Webhook\EventNormalizer;

use Akeneo\Connectivity\Connection\Domain\Webhook\EventNormalizer\EventNormalizer;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\Event;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventNormalizerSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(EventNormalizer::class);
    }

    public function it_supports_an_event(EventInterface $event): void
    {
        $this->supports($event)->shouldReturn(true);
    }

    public function it_normalizes_an_event(): void
    {
        $event = new class(
            Author::fromNameAndType('julia', Author::TYPE_UI),
            [],
            0,
            '9979c367-595d-42ad-9070-05f62f31f49b'
        ) extends Event
        {
            public function getName(): string
            {
                return 'my_event';
            }
        };

        $this->normalize($event)->shouldReturn([
            'action' => 'my_event',
            'event_id' => '9979c367-595d-42ad-9070-05f62f31f49b',
            'event_datetime' => '1970-01-01T00:00:00+00:00',
            'author' => 'julia',
            'author_type' => 'ui',
        ]);
    }
}
