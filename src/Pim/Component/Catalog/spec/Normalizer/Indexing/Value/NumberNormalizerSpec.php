<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing\Value;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Normalizer\Indexing\Product\ProductNormalizer;
use Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class NumberNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(\Pim\Component\Catalog\Normalizer\Indexing\Value\NumberNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_support_number_product_value(
        ValueInterface $numberValue,
        ValueInterface $textValue,
        AttributeInterface $numberAttribute,
        AttributeInterface $textAttribute
    ) {
        $numberValue->getAttribute()->willReturn($numberAttribute);
        $textValue->getAttribute()->willReturn($textAttribute);

        $numberAttribute->getBackendType()->willReturn('decimal');
        $textAttribute->getBackendType()->willReturn('text');

        $this->supportsNormalization(new \stdClass(), ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);

        $this->supportsNormalization($textValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn(false);
        $this->supportsNormalization($numberValue, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($numberValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->shouldReturn(true);

        $this->supportsNormalization(new \stdClass(), ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($textValue, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($numberValue, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(true);

    }

    function it_normamlizes_an_empty_number_product_value_with_no_locale_and_no_channel(
        ValueInterface $integerValue,
        AttributeInterface $integerAttribute
    ) {
        $integerValue->getAttribute()->willReturn($integerAttribute);
        $integerValue->getLocale()->willReturn(null);
        $integerValue->getScope()->willReturn(null);
        $integerValue->getData()->willReturn(null);

        $integerAttribute->isDecimalsAllowed()->willReturn(false);
        $integerAttribute->getCode()->willReturn('box_quantity');
        $integerAttribute->getBackendType()->willReturn('decimal');

        $this->normalize($integerValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn([
            'box_quantity-decimal' => [
                '<all_channels>' => [
                    '<all_locales>' => null
                ]
            ]
        ]);
    }

    function it_normalize_an_integer_product_value_with_no_locale_and_no_channel(
        ValueInterface $integerValue,
        AttributeInterface $integerAttribute
    ) {
        $integerValue->getAttribute()->willReturn($integerAttribute);
        $integerValue->getLocale()->willReturn(null);
        $integerValue->getScope()->willReturn(null);
        $integerValue->getData()->willReturn(12);

        $integerAttribute->isDecimalsAllowed()->willReturn(false);
        $integerAttribute->getCode()->willReturn('box_quantity');
        $integerAttribute->getBackendType()->willReturn('decimal');

        $this->normalize($integerValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn([
            'box_quantity-decimal' => [
                '<all_channels>' => [
                    '<all_locales>' => '12'
                ]
            ]
        ]);
    }

    function it_normalize_a_decimal_product_value_with_no_locale_and_no_channel(
        ValueInterface $decimalValue,
        AttributeInterface $decimalAttribute
    ){
        $decimalValue->getAttribute()->willReturn($decimalAttribute);
        $decimalValue->getLocale()->willReturn(null);
        $decimalValue->getScope()->willReturn(null);
        $decimalValue->getData()->willReturn('12.4999');

        $decimalAttribute->isDecimalsAllowed()->willReturn(true);
        $decimalAttribute->getCode()->willReturn('size');
        $decimalAttribute->getBackendType()->willReturn('decimal');

        $this->normalize($decimalValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn([
            'size-decimal' => [
                '<all_channels>' => [
                    '<all_locales>' => '12.4999'
                ]
            ]
        ]);
    }

    function it_normalizes_a_decimal_product_value_with_locale(
        ValueInterface $decimalValue,
        AttributeInterface $decimalAttribute
    ) {
        $decimalValue->getAttribute()->willReturn($decimalAttribute);
        $decimalValue->getLocale()->willReturn('en_US');
        $decimalValue->getScope()->willReturn(null);
        $decimalValue->getData()->willReturn('12.4999');

        $decimalAttribute->isDecimalsAllowed()->willReturn(true);
        $decimalAttribute->getCode()->willReturn('size');
        $decimalAttribute->getBackendType()->willReturn('decimal');

        $this->normalize($decimalValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn([
            'size-decimal' => [
                '<all_channels>' => [
                    'en_US' => '12.4999'
                ]
            ]
        ]);
    }

    function it_normalizes_a_integer_product_value_with_locale(
        ValueInterface $decimalValue,
        AttributeInterface $decimalAttribute
    ) {
        $decimalValue->getAttribute()->willReturn($decimalAttribute);
        $decimalValue->getLocale()->willReturn(null);
        $decimalValue->getScope()->willReturn('ecommerce');
        $decimalValue->getData()->willReturn(12);

        $decimalAttribute->isDecimalsAllowed()->willReturn(false);
        $decimalAttribute->getCode()->willReturn('size');
        $decimalAttribute->getBackendType()->willReturn('decimal');

        $this->normalize($decimalValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn([
            'size-decimal' => [
                'ecommerce' => [
                    '<all_locales>' => '12'
                ]
            ]
        ]);
    }

    function it_normalizes_a_integer_product_value_with_locale_and_channel(
        ValueInterface $decimalValue,
        AttributeInterface $decimalAttribute
    ) {
        $decimalValue->getAttribute()->willReturn($decimalAttribute);
        $decimalValue->getLocale()->willReturn('fr_FR');
        $decimalValue->getScope()->willReturn('ecommerce');
        $decimalValue->getData()->willReturn(12);

        $decimalAttribute->isDecimalsAllowed()->willReturn(false);
        $decimalAttribute->getCode()->willReturn('size');
        $decimalAttribute->getBackendType()->willReturn('decimal');

        $this->normalize($decimalValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn([
            'size-decimal' => [
                'ecommerce' => [
                    'fr_FR' => '12'
                ]
            ]
        ]);
    }
}

