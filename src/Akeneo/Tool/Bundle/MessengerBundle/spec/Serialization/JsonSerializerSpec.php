<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MessengerBundle\Serialization;

use Akeneo\Tool\Bundle\MessengerBundle\Message\OrderedMessageInterface;
use Akeneo\Tool\Bundle\MessengerBundle\Serialization\JsonSerializer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JsonSerializerSpec extends ObjectBehavior
{
    public function let(NormalizerInterface $normalizer, DenormalizerInterface $denormalizer): void
    {
        $this->beConstructedWith([$normalizer, $denormalizer]);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(JsonSerializer::class);
    }

    public function it_decodes_an_envelope($denormalizer): void
    {
        $encodedEnvelope = [
            'body' => '{"some_property":"Some value!"}',
            'headers' => [
                'class' => \stdClass::class
            ]
        ];

        $denormalizer->supportsDenormalization(['some_property' => 'Some value!'], \stdClass::class, 'json', [])
            ->willReturn(true);

        $message = new \stdClass();
        $denormalizer->denormalize(['some_property' => 'Some value!'], \stdClass::class, 'json', [])
            ->willReturn($message);

        $expectedEnvelope = new Envelope($message);

        $this->decode($encodedEnvelope)
            ->shouldBeLike($expectedEnvelope);
    }

    public function it_encodes_an_envelope($normalizer): void
    {
        $message = new \stdClass();
        $envelope = new Envelope($message);

        $normalizer->supportsNormalization($message, 'json', [])
            ->willReturn(true);

        $normalizer->normalize($message, 'json', [])
            ->willReturn(['some_property' => 'Some value!']);

        $this->encode($envelope)
            ->shouldReturn([
                'body' => '{"some_property":"Some value!"}',
                'headers' => ['class' => \stdClass::class],
                'orderingKey' => null,
            ]);
    }

    public function it_encodes_an_envelope_with_ordering_key($normalizer): void
    {
        $message = new class implements OrderedMessageInterface {
            public function getOrderingKey(): string
            {
                return 'the_key';
            }

        };
        $envelope = new Envelope($message);

        $normalizer->supportsNormalization($message, 'json', [])
            ->willReturn(true);

        $normalizer->normalize($message, 'json', [])
            ->willReturn(['some_property' => 'Some value!']);

        $this->encode($envelope)
            ->shouldReturn([
                'body' => '{"some_property":"Some value!"}',
                'headers' => ['class' => get_class($message)],
                'orderingKey' => 'the_key',
            ]);
    }
}
