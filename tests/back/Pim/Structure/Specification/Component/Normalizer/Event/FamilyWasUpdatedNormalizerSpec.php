<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Component\Normalizer\Event;

use Akeneo\Pim\Structure\Component\Event\FamilyWasUpdated;
use Akeneo\Pim\Structure\Component\Normalizer\Event\FamilyWasUpdatedNormalizer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FamilyWasUpdatedNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer_and_a_denormalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
        $this->shouldImplement(DenormalizerInterface::class);
        $this->shouldHaveType(FamilyWasUpdatedNormalizer::class);
    }

    function it_supports_only_family_was_updated_event_for_normalization()
    {
        $date = new \DateTimeImmutable();
        $event = new FamilyWasUpdated(1, 'name', $date);

        $this->supportsNormalization($event)->shouldReturn(true);
        $this->supportsNormalization(new \stdClass())->shouldReturn(false);
    }

    function it_normalizes_an_event()
    {
        $date = new \DateTimeImmutable();
        $event = new FamilyWasUpdated(1, 'name', $date);

        $this->normalize($event)->shouldReturn([
            'id' => 1,
            'code' => 'name',
            'updated_at' => $date->format(\DateTimeInterface::ATOM),
        ]);
    }

    function it_supports_only_family_was_updated_for_denormalization()
    {
        $this->supportsDenormalization([], FamilyWasUpdated::class)->shouldReturn(true);
        $this->supportsDenormalization([], \stdClass::class)->shouldReturn(false);
    }

    function it_denormalizes()
    {
        $date = new \DateTimeImmutable('2020-11-24T22:02:12+00:00');
        $event = new FamilyWasUpdated(1, 'name', $date);

        $this->denormalize([
            'id' => 1,
            'code' => 'name',
            'updated_at' => $date->format(\DateTimeInterface::ATOM),
        ], FamilyWasUpdated::class)->shouldBeLike($event);
    }
}
