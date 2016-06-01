<?php

namespace spec\Pim\Component\Connector\Denormalizer\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Connector\Denormalizer\Flat\ProductValuesDenormalizer;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AttributeColumnInfoExtractor;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ProductValuesDenormalizerSpec extends ObjectBehavior
{
    const FORMAT_CSV  = 'csv';
    const VALUE_CLASS = 'Pim\Component\Catalog\Model\ProductValue';

    function let(DenormalizerInterface $valueDenormalizer, AttributeColumnInfoExtractor $fieldExtractor)
    {
        $this->beConstructedWith($valueDenormalizer, $fieldExtractor, self::VALUE_CLASS);
    }

    function it_is_a_denormalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_supports_denormalization_in_csv_of_a_collection_of_variant_group_values()
    {
        $this->supportsDenormalization(
            [],
            ProductValuesDenormalizer::PRODUCT_VALUES_TYPE,
            self::FORMAT_CSV
        )->shouldBe(true);

        $this->supportsDenormalization(
            [],
            'other_type',
            self::FORMAT_CSV
        )->shouldBe(false);
    }

    function it_denormalizes_variant_group_values($fieldExtractor, $valueDenormalizer, AttributeInterface $description)
    {
        $fieldExtractor->extractColumnInfo('description-ecommerce-en_US')
            ->willReturn(['attribute' => $description, 'locale_code' => 'en_US', 'scope_code' => 'ecommerce'])
            ->shouldBeCalled();
        $fieldExtractor->extractColumnInfo('description-ecommerce-fr_FR')
            ->willReturn(['attribute' => $description, 'locale_code' => 'fr_FR', 'scope_code' => 'ecommerce'])
            ->shouldBeCalled();

        $description->getCode()->willReturn('description');
        $description->isLocalizable()->willReturn(true);
        $description->isScopable()->willReturn(true);

        $valueDenormalizer->denormalize(
            'My en_US desc',
            self::VALUE_CLASS,
            self::FORMAT_CSV,
            Argument::any()
        )->shouldBeCalled();

        $valueDenormalizer->denormalize(
            'My fr_FR desc',
            self::VALUE_CLASS,
            self::FORMAT_CSV,
            Argument::any()
        )->shouldBeCalled();

        $csvData = [
            'description-ecommerce-en_US' => 'My en_US desc',
            'description-ecommerce-fr_FR' => 'My fr_FR desc',
        ];
        $this->denormalize($csvData, 'ProductValue[]', 'csv');
    }
}
