<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Component\EventQueue;

use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Platform\Component\EventQueue\BulkEventNormalizer;
use Akeneo\Platform\Component\EventQueue\Event;
use Akeneo\Platform\Component\EventQueue\EventNormalizer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class BulkEventNormalizerSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(new EventNormalizer());
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(BulkEventNormalizer::class);
    }

    public function it_is_a_normalizer(): void
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    public function it_supports_normalization_of_bulk_event(): void
    {
        $event = new BulkEvent([]);

        $this->supportsNormalization($event)->shouldReturn(true);
    }

    public function it_does_not_support_normalization_of_non_bulk_event(): void
    {
        $object = new \stdClass();

        $this->supportsNormalization($object)->shouldReturn(false);
    }

    public function it_normalizes_a_bulk_event()
    {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);

        $event = new class ($author, ['data'], 0, 'e0e4c95d-9646-40d7-be2b-d9b14fc0c6ba') extends Event
        {
            public function getName(): string
            {
                return 'event_name';
            }
        };

        $bulkEvent = new BulkEvent([$event]);

        $expected = [
            [
                'type' => \get_class($event),
                'name' => 'event_name',
                'author' => 'julia',
                'author_type' => 'ui',
                'data' => ['data'],
                'timestamp' => 0,
                'uuid' => 'e0e4c95d-9646-40d7-be2b-d9b14fc0c6ba',
            ],
        ];

        $this->normalize($bulkEvent)->shouldReturn($expected);
    }

    public function it_is_a_denormalizer(): void
    {
        $this->shouldImplement(DenormalizerInterface::class);
    }

    public function it_supports_denormalization_of_bulk_event(): void
    {
        $this->supportsDenormalization([], BulkEvent::class)->shouldReturn(true);
    }

    public function it_does_not_support_denormalization_of_bulk_non_event(): void
    {
        $this->supportsDenormalization([], \stdClass::class)->shouldReturn(false);
    }

    public function it_denormalizes_a_bulk_event()
    {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);

        $event = new class ($author, ['data'], 0, 'e0e4c95d-9646-40d7-be2b-d9b14fc0c6ba') extends Event
        {
            public function getName(): string
            {
                return 'event_name';
            }
        };

        $bulkEvent = new BulkEvent([$event]);

        $data = [
            [
                'type' => \get_class($event),
                'name' => 'event_name',
                'author' => 'julia',
                'author_type' => 'ui',
                'data' => ['data'],
                'timestamp' => 0,
                'uuid' => 'e0e4c95d-9646-40d7-be2b-d9b14fc0c6ba',
            ]
        ];

        $this->denormalize($data, BulkEvent::class)->shouldBeLike($bulkEvent);
    }
}
