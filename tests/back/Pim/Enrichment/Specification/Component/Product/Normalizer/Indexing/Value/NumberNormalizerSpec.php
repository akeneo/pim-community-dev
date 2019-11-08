<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\NumberNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class NumberNormalizerSpec extends ObjectBehavior
{
    function let(GetAttributes $getAttributes)
    {
        $this->beConstructedWith($getAttributes);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(NumberNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_support_number_product_value(
        ValueInterface $numberValue,
        ValueInterface $textValue,
        GetAttributes $getAttributes
    ) {
        $numberValue->getAttributeCode()->willReturn('my_number_attribute');
        $textValue->getAttributeCode()->willReturn('my_text_attribute');

        $getAttributes->forCode('my_number_attribute')->willReturn(new Attribute(
            'my_number_attribute',
            'pim_catalog_number',
            [],
            false,
            false,
            null,
            true,
            'decimal',
            []
        ));
        $getAttributes->forCode('my_text_attribute')->willReturn(new Attribute(
            'my_text_attribute',
            'pim_catalog_text',
            [],
            false,
            false,
            null,
            true,
            'text',
            []
        ));

        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);

        $this->supportsNormalization($numberValue, 'whatever')->shouldReturn(false);

        $this->supportsNormalization(new \stdClass(), ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($textValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($numberValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(true);

    }

    function it_normamlizes_an_empty_number_product_value_with_no_locale_and_no_channel(
        ValueInterface $integerValue,
        GetAttributes $getAttributes
    ) {
        $integerValue->getAttributeCode()->willReturn('my_number_attribute');
        $integerValue->getLocaleCode()->willReturn(null);
        $integerValue->getScopeCode()->willReturn(null);
        $integerValue->getData()->willReturn(null);

        $getAttributes->forCode('my_number_attribute')->willReturn(new Attribute(
            'my_number_attribute',
            'pim_catalog_number',
            [],
            false,
            false,
            null,
            false,
            'decimal',
            []
        ));

        $this->normalize($integerValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'my_number_attribute-decimal' => [
                '<all_channels>' => [
                    '<all_locales>' => null
                ]
            ]
        ]);
    }

    function it_normalize_an_integer_product_value_with_no_locale_and_no_channel(
        ValueInterface $integerValue,
        GetAttributes $getAttributes
    ) {
        $integerValue->getAttributeCode()->willReturn('my_number_attribute');
        $integerValue->getLocaleCode()->willReturn(null);
        $integerValue->getScopeCode()->willReturn(null);
        $integerValue->getData()->willReturn(12);

        $getAttributes->forCode('my_number_attribute')->willReturn(new Attribute(
            'my_number_attribute',
            'pim_catalog_number',
            [],
            false,
            false,
            null,
            false,
            'decimal',
            []
        ));

        $this->normalize($integerValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'my_number_attribute-decimal' => [
                '<all_channels>' => [
                    '<all_locales>' => '12'
                ]
            ]
        ]);
    }

    function it_normalize_a_decimal_product_value_with_no_locale_and_no_channel(
        ValueInterface $decimalValue,
        GetAttributes $getAttributes
    ){
        $decimalValue->getAttributeCode()->willReturn('my_number_attribute');
        $decimalValue->getLocaleCode()->willReturn(null);
        $decimalValue->getScopeCode()->willReturn(null);
        $decimalValue->getData()->willReturn('12.4999');

        $getAttributes->forCode('my_number_attribute')->willReturn(new Attribute(
            'my_number_attribute',
            'pim_catalog_number',
            [],
            false,
            false,
            null,
            true,
            'decimal',
            []
        ));

        $this->normalize($decimalValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'my_number_attribute-decimal' => [
                '<all_channels>' => [
                    '<all_locales>' => '12.4999'
                ]
            ]
        ]);
    }

    function it_normalizes_a_decimal_product_value_with_locale(
        ValueInterface $decimalValue,
        GetAttributes $getAttributes
    ) {
        $decimalValue->getAttributeCode()->willReturn('my_number_attribute');
        $decimalValue->getLocaleCode()->willReturn('en_US');
        $decimalValue->getScopeCode()->willReturn(null);
        $decimalValue->getData()->willReturn('12.4999');

        $getAttributes->forCode('my_number_attribute')->willReturn(new Attribute(
            'my_number_attribute',
            'pim_catalog_number',
            [],
            false,
            false,
            null,
            true,
            'decimal',
            []
        ));

        $this->normalize($decimalValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'my_number_attribute-decimal' => [
                '<all_channels>' => [
                    'en_US' => '12.4999'
                ]
            ]
        ]);
    }

    function it_normalizes_a_integer_product_value_with_locale(
        ValueInterface $decimalValue,
        GetAttributes $getAttributes
    ) {
        $decimalValue->getAttributeCode()->willReturn('my_number_attribute');
        $decimalValue->getLocaleCode()->willReturn(null);
        $decimalValue->getScopeCode()->willReturn('ecommerce');
        $decimalValue->getData()->willReturn(12);

        $getAttributes->forCode('my_number_attribute')->willReturn(new Attribute(
            'my_number_attribute',
            'pim_catalog_number',
            [],
            false,
            false,
            null,
            false,
            'decimal',
            []
        ));

        $this->normalize($decimalValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'my_number_attribute-decimal' => [
                'ecommerce' => [
                    '<all_locales>' => '12'
                ]
            ]
        ]);
    }

    function it_normalizes_a_integer_product_value_with_locale_and_channel(
        ValueInterface $decimalValue,
        GetAttributes $getAttributes
    ) {
        $decimalValue->getAttributeCode()->willReturn('my_number_attribute');
        $decimalValue->getLocaleCode()->willReturn('fr_FR');
        $decimalValue->getScopeCode()->willReturn('ecommerce');
        $decimalValue->getData()->willReturn(12);

        $getAttributes->forCode('my_number_attribute')->willReturn(new Attribute(
            'my_number_attribute',
            'pim_catalog_number',
            [],
            false,
            false,
            null,
            false,
            'decimal',
            []
        ));

        $this->normalize($decimalValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'my_number_attribute-decimal' => [
                'ecommerce' => [
                    'fr_FR' => '12'
                ]
            ]
        ]);
    }
}

