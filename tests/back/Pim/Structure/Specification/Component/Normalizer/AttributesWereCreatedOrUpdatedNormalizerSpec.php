<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Component\Normalizer\Event;

use Akeneo\Pim\Structure\Component\Event\AttributesWereCreatedOrUpdated;
use Akeneo\Pim\Structure\Component\Event\AttributeWasCreated;
use Akeneo\Pim\Structure\Component\Event\AttributeWasUpdated;
use Akeneo\Pim\Structure\Component\Normalizer\Event\AttributesWereCreatedOrUpdatedNormalizer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AttributesWereCreatedOrUpdatedNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer_and_a_denormalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
        $this->shouldImplement(DenormalizerInterface::class);
        $this->shouldHaveType(AttributesWereCreatedOrUpdatedNormalizer::class);
    }

    function it_supports_only_attributes_were_created_or_updated_for_normalization()
    {
        $date = new \DateTimeImmutable();
        $attributesWereCreatedOrUpdated = new AttributesWereCreatedOrUpdated([
            new AttributeWasCreated(1, 'name', $date),
            new AttributeWasUpdated(2, 'desc', $date),
        ]);

        $this->supportsNormalization($attributesWereCreatedOrUpdated)->shouldReturn(true);
        $this->supportsNormalization(new \stdClass())->shouldReturn(false);
    }

    function it_normalizes_an_object()
    {
        $date = new \DateTimeImmutable();
        $attributesWereCreatedOrUpdated = new AttributesWereCreatedOrUpdated([
            new AttributeWasCreated(1, 'name', $date),
            new AttributeWasUpdated(2, 'desc', $date),
        ]);

        $this->normalize($attributesWereCreatedOrUpdated)->shouldReturn(['events' => [
            [
                'id' => 1,
                'code' => 'name',
                'created_at' => $date->format(\DateTimeInterface::ATOM),
            ],
            [
                'id' => 2,
                'code' => 'desc',
                'updated_at' => $date->format(\DateTimeInterface::ATOM),
            ],
        ]]);
    }

    function it_supports_only_attributes_were_created_or_updated_for_denormalization()
    {
        $this->supportsDenormalization([], AttributesWereCreatedOrUpdated::class)->shouldReturn(true);
        $this->supportsDenormalization([], \stdClass::class)->shouldReturn(false);
    }

    function it_denormalizes()
    {
        $date = new \DateTimeImmutable('2020-11-24T22:02:12+00:00');
        $attributesWereCreatedOrUpdated = new AttributesWereCreatedOrUpdated([
            new AttributeWasCreated(1, 'name', $date),
            new AttributeWasUpdated(2, 'desc', $date),
        ]);

        $this->denormalize(['events' => [
            [
                'id' => 1,
                'code' => 'name',
                'created_at' => $date->format(\DateTimeInterface::ATOM),
            ],
            [
                'id' => 2,
                'code' => 'desc',
                'updated_at' => $date->format(\DateTimeInterface::ATOM),
            ],
        ]], AttributesWereCreatedOrUpdated::class)->shouldBeLike($attributesWereCreatedOrUpdated);
    }
}
