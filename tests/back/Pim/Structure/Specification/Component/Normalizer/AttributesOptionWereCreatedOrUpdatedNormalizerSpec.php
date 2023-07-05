<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Specification\Akeneo\Pim\Structure\Component\Normalizer;

use Akeneo\Pim\Structure\Bundle\Event\AttributeOptionWasCreated;
use Akeneo\Pim\Structure\Bundle\Event\AttributeOptionWasUpdated;
use Akeneo\Pim\Structure\Bundle\Event\AttributesOptionWereCreatedOrUpdated;
use Akeneo\Pim\Structure\Component\Normalizer\AttributesOptionWereCreatedOrUpdatedNormalizer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class AttributesOptionWereCreatedOrUpdatedNormalizerSpec extends ObjectBehavior
{
    public function it_is_a_normalizer_and_a_denormalizer(): void
    {
        $this->shouldImplement(NormalizerInterface::class);
        $this->shouldImplement(DenormalizerInterface::class);
        $this->shouldHaveType(AttributesOptionWereCreatedOrUpdatedNormalizer::class);
    }

    public function it_supports_only_attributes_option_were_created_or_updated_for_normalization(): void
    {
        $date = new \DateTimeImmutable();
        $attributesOptionWereCreatedOrUpdated = new AttributesOptionWereCreatedOrUpdated([
            new AttributeOptionWasCreated(1, 'color', 'name', $date),
            new AttributeOptionWasUpdated(2, 'size', 'name', $date),
        ]);

        $this->supportsNormalization($attributesOptionWereCreatedOrUpdated)->shouldReturn(true);
        $this->supportsNormalization(new \stdClass())->shouldReturn(false);
    }

    public function it_normalizes_an_object(): void
    {
        $date = new \DateTimeImmutable();
        $attributesOptionWereCreatedOrUpdated = new AttributesOptionWereCreatedOrUpdated([
            new AttributeOptionWasCreated(1, 'color', 'name', $date),
            new AttributeOptionWasUpdated(2, 'size', 'name', $date),
        ]);

        $this->normalize($attributesOptionWereCreatedOrUpdated)->shouldReturn(['events' => [
            [
                'id' => 1,
                'code' => 'color',
                'attribute_code' => 'name',
                'created_at' => $date->format(\DateTimeInterface::ATOM),
            ],
            [
                'id' => 2,
                'code' => 'size',
                'attribute_code' => 'name',
                'updated_at' => $date->format(\DateTimeInterface::ATOM),
            ],
        ]]);
    }

    public function it_supports_only_attributes_option_were_created_or_updated_for_denormalization(): void
    {
        $this->supportsDenormalization([], AttributesOptionWereCreatedOrUpdated::class)->shouldReturn(true);
        $this->supportsDenormalization([], \stdClass::class)->shouldReturn(false);
    }

    public function it_denormalizes(): void
    {
        $date = new \DateTimeImmutable('2020-11-24T22:02:12+00:00');
        $attributesOptionWereCreatedOrUpdated = new AttributesOptionWereCreatedOrUpdated([
            new AttributeOptionWasCreated(1, 'color', 'name', $date),
            new AttributeOptionWasUpdated(2, 'size', 'name', $date),
        ]);

        $this->denormalize(['events' => [
            [
                'id' => 1,
                'code' => 'color',
                'attribute_code' => 'name',
                'created_at' => $date->format(\DateTimeInterface::ATOM),
            ],
            [
                'id' => 2,
                'code' => 'size',
                'attribute_code' => 'name',
                'updated_at' => $date->format(\DateTimeInterface::ATOM),
            ],
        ]], AttributesOptionWereCreatedOrUpdated::class)->shouldBeLike($attributesOptionWereCreatedOrUpdated);
    }
}
