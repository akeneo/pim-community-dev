<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MessengerBundle\Serialization;

use Akeneo\Tool\Bundle\MessengerBundle\Serialization\JsonSerializer;
use Akeneo\Tool\Component\Messenger\Stamp\CustomHeaderStamp;
use Akeneo\Tool\Bundle\MessengerBundle\Stamp\TenantIdStamp;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
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
            ]);
    }

    public function it_encodes_an_envelope_with_tenant_id($normalizer): void
    {
        $message = new \stdClass();
        $envelope = new Envelope($message, [new TenantIdStamp('my_tenant_id_value')]);

        $normalizer->supportsNormalization($message, 'json', [])
            ->willReturn(true);

        $normalizer->normalize($message, 'json', [])
            ->willReturn(['some_property' => 'Some value!']);

        $this->encode($envelope)
            ->shouldReturn([
                'body' => '{"some_property":"Some value!"}',
                'headers' => [
                    'class' => \stdClass::class,
                    'tenant_id' => 'my_tenant_id_value',
                ],
            ]);
    }

    public function it_encodes_an_envelope_with_retry($normalizer): void
    {
        $message = new \stdClass();
        $envelope = new Envelope($message, [
            new RedeliveryStamp(5),
        ]);

        $normalizer->supportsNormalization($message, 'json', [])
            ->willReturn(true);

        $normalizer->normalize($message, 'json', [])
            ->willReturn(['some_property' => 'Some value!']);

        $this->encode($envelope)
            ->shouldReturn([
                'body' => '{"some_property":"Some value!"}',
                'headers' => [
                    'class' => \stdClass::class,
                    'retry_count' => '5',
                ],
            ]);
    }

    public function it_encodes_an_envelope_with_custom_header($normalizer): void
    {
        $message = new \stdClass();
        $envelope = new Envelope($message, [
            $this->buildCustomStamp(),
        ]);

        $normalizer->supportsNormalization($message, 'json', [])
            ->willReturn(true);

        $normalizer->normalize($message, 'json', [])
            ->willReturn(['some_property' => 'Some value!']);

        $this->encode($envelope)
            ->shouldReturn([
                'body' => '{"some_property":"Some value!"}',
                'headers' => [
                    'customHeader' => 'customerHeaderValue',
                    'class' => \stdClass::class,
                ],
            ]);
    }

    public function it_throw_an_exception_when_the_same_custom_header_is_used_twice($normalizer): void
    {
        $message = new \stdClass();
        $envelope = new Envelope($message, [
            $this->buildCustomStamp(),
            $this->buildCustomStamp(),
        ]);

        $normalizer->supportsNormalization($message, 'json', [])
            ->willReturn(true);

        $normalizer->normalize($message, 'json', [])
            ->willReturn(['some_property' => 'Some value!']);

        $this->shouldThrow(\LogicException::class)
            ->during('encode', [$envelope]);
    }

    private function buildCustomStamp(): CustomHeaderStamp
    {
        return new class implements CustomHeaderStamp {
            public function header(): string
            {
                return 'customHeader';
            }

            public function value(): string
            {
                return 'customerHeaderValue';
            }
        };
    }
}
