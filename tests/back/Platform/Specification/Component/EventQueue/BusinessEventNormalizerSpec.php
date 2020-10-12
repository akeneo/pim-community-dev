<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Component\EventQueue;

use Akeneo\Platform\Component\EventQueue\BusinessEvent;
use Akeneo\Platform\Component\EventQueue\BusinessEventNormalizer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class BusinessEventNormalizerSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(BusinessEventNormalizer::class);
    }

    public function it_is_a_normalizer(): void
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    public function it_supports_normalization_of_business_event(): void
    {
        $businessEvent = new class ('ecommerce_connection', 'api', ['data'], 0, 'e0e4c95d-9646-40d7-be2b-d9b14fc0c6ba') extends BusinessEvent
        {
            public function name(): string
            {
                return 'event_name';
            }
        };

        $this->supportsNormalization($businessEvent)
            ->shouldReturn(true);
    }

    public function it_does_not_supports_normalization_of_non_business_event(): void
    {
        $object = new \stdClass();

        $this->supportsNormalization($object)
            ->shouldReturn(false);
    }

    public function it_normalizes_a_business_event()
    {
        $businessEvent = new class ('ecommerce_connection', 'api', ['data'], 0, 'e0e4c95d-9646-40d7-be2b-d9b14fc0c6ba') extends BusinessEvent
        {
            public function name(): string
            {
                return 'event_name';
            }
        };

        $expected = [
            'name' => 'event_name',
            'author' => 'ecommerce_connection',
            'author_type' => 'api',
            'data' => ['data'],
            'timestamp' => 0,
            'uuid' => 'e0e4c95d-9646-40d7-be2b-d9b14fc0c6ba',
        ];

        $this->normalize($businessEvent)
            ->shouldReturn($expected);
    }

    public function it_is_a_denormalizer(): void
    {
        $this->shouldImplement(DenormalizerInterface::class);
    }

    public function it_supports_denormalization_of_business_event(): void
    {
        $businessEvent = new class ('author', 'api', ['data'], 0, 'e0e4c95d-9646-40d7-be2b-d9b14fc0c6ba') extends BusinessEvent
        {
            public function name(): string
            {
                return 'event_name';
            }
        };

        $this->supportsDenormalization([], get_class($businessEvent))
            ->shouldReturn(true);
    }

    public function it_does_not_supports_denormalization_of_non_business_event(): void
    {
        $this->supportsDenormalization([], \stdClass::class)
            ->shouldReturn(false);
    }

    public function it_denormalizes_a_business_event()
    {
        $businessEvent = new class ('author', 'api', ['data'], 0, 'e0e4c95d-9646-40d7-be2b-d9b14fc0c6ba') extends BusinessEvent
        {
            public function name(): string
            {
                return 'event_name';
            }
        };

        $data = [
            'name' => 'event_name',
            'author' => 'author',
            'author_type' => 'api',
            'data' => ['data'],
            'timestamp' => 0,
            'uuid' => 'e0e4c95d-9646-40d7-be2b-d9b14fc0c6ba',
        ];

        $this->denormalize($data, get_class($businessEvent))
            ->shouldBeLike($businessEvent);
    }
}
