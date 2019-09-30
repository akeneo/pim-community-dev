<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\NumberNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class NumberNormalizerSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
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
        AttributeInterface $numberAttribute,
        AttributeInterface $textAttribute,
        $attributeRepository
    ) {
        $numberValue->getAttributeCode()->willReturn('my_number_attribute');
        $textValue->getAttributeCode()->willReturn('my_text_attribute');

        $numberAttribute->getBackendType()->willReturn('decimal');
        $textAttribute->getBackendType()->willReturn('text');

        $attributeRepository->findOneByIdentifier('my_number_attribute')->willReturn($numberAttribute);
        $attributeRepository->findOneByIdentifier('my_text_attribute')->willReturn($textAttribute);

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
        AttributeInterface $integerAttribute,
        $attributeRepository
    ) {
        $integerValue->getAttributeCode()->willReturn('my_integer_attribute');
        $integerValue->getLocaleCode()->willReturn(null);
        $integerValue->getScopeCode()->willReturn(null);
        $integerValue->getData()->willReturn(null);

        $integerAttribute->isDecimalsAllowed()->willReturn(false);
        $integerAttribute->getCode()->willReturn('box_quantity');
        $integerAttribute->getBackendType()->willReturn('decimal');
        $attributeRepository->findOneByIdentifier('my_integer_attribute')->willReturn($integerAttribute);

        $this->normalize($integerValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'box_quantity-decimal' => [
                '<all_channels>' => [
                    '<all_locales>' => null
                ]
            ]
        ]);
    }

    function it_normalize_an_integer_product_value_with_no_locale_and_no_channel(
        ValueInterface $integerValue,
        AttributeInterface $integerAttribute,
        $attributeRepository
    ) {
        $integerValue->getAttributeCode()->willReturn('my_integer_attribute');
        $integerValue->getLocaleCode()->willReturn(null);
        $integerValue->getScopeCode()->willReturn(null);
        $integerValue->getData()->willReturn(12);

        $integerAttribute->isDecimalsAllowed()->willReturn(false);
        $integerAttribute->getCode()->willReturn('box_quantity');
        $integerAttribute->getBackendType()->willReturn('decimal');
        $attributeRepository->findOneByIdentifier('my_integer_attribute')->willReturn($integerAttribute);

        $this->normalize($integerValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'box_quantity-decimal' => [
                '<all_channels>' => [
                    '<all_locales>' => '12'
                ]
            ]
        ]);
    }

    function it_normalize_a_decimal_product_value_with_no_locale_and_no_channel(
        ValueInterface $decimalValue,
        AttributeInterface $decimalAttribute,
        $attributeRepository
    ){
        $decimalValue->getAttributeCode()->willReturn('my_decimal_attribute');
        $decimalValue->getLocaleCode()->willReturn(null);
        $decimalValue->getScopeCode()->willReturn(null);
        $decimalValue->getData()->willReturn('12.4999');

        $decimalAttribute->isDecimalsAllowed()->willReturn(true);
        $decimalAttribute->getCode()->willReturn('size');
        $decimalAttribute->getBackendType()->willReturn('decimal');
        $attributeRepository->findOneByIdentifier('my_decimal_attribute')->willReturn($decimalAttribute);

        $this->normalize($decimalValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'size-decimal' => [
                '<all_channels>' => [
                    '<all_locales>' => '12.4999'
                ]
            ]
        ]);
    }

    function it_normalizes_a_decimal_product_value_with_locale(
        ValueInterface $decimalValue,
        AttributeInterface $decimalAttribute,
        $attributeRepository
    ) {
        $decimalValue->getAttributeCode()->willReturn('my_decimal_attribute');
        $decimalValue->getLocaleCode()->willReturn('en_US');
        $decimalValue->getScopeCode()->willReturn(null);
        $decimalValue->getData()->willReturn('12.4999');

        $decimalAttribute->isDecimalsAllowed()->willReturn(true);
        $decimalAttribute->getCode()->willReturn('size');
        $decimalAttribute->getBackendType()->willReturn('decimal');
        $attributeRepository->findOneByIdentifier('my_decimal_attribute')->willReturn($decimalAttribute);

        $this->normalize($decimalValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'size-decimal' => [
                '<all_channels>' => [
                    'en_US' => '12.4999'
                ]
            ]
        ]);
    }

    function it_normalizes_a_integer_product_value_with_locale(
        ValueInterface $decimalValue,
        AttributeInterface $decimalAttribute,
        $attributeRepository
    ) {
        $decimalValue->getAttributeCode()->willReturn('my_decimal_attribute');
        $decimalValue->getLocaleCode()->willReturn(null);
        $decimalValue->getScopeCode()->willReturn('ecommerce');
        $decimalValue->getData()->willReturn(12);

        $decimalAttribute->isDecimalsAllowed()->willReturn(false);
        $decimalAttribute->getCode()->willReturn('size');
        $decimalAttribute->getBackendType()->willReturn('decimal');
        $attributeRepository->findOneByIdentifier('my_decimal_attribute')->willReturn($decimalAttribute);

        $this->normalize($decimalValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'size-decimal' => [
                'ecommerce' => [
                    '<all_locales>' => '12'
                ]
            ]
        ]);
    }

    function it_normalizes_a_integer_product_value_with_locale_and_channel(
        ValueInterface $decimalValue,
        AttributeInterface $decimalAttribute,
        $attributeRepository
    ) {
        $decimalValue->getAttributeCode()->willReturn('my_decimal_attribute');
        $decimalValue->getLocaleCode()->willReturn('fr_FR');
        $decimalValue->getScopeCode()->willReturn('ecommerce');
        $decimalValue->getData()->willReturn(12);

        $decimalAttribute->isDecimalsAllowed()->willReturn(false);
        $decimalAttribute->getCode()->willReturn('size');
        $decimalAttribute->getBackendType()->willReturn('decimal');
        $attributeRepository->findOneByIdentifier('my_decimal_attribute')->willReturn($decimalAttribute);

        $this->normalize($decimalValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'size-decimal' => [
                'ecommerce' => [
                    'fr_FR' => '12'
                ]
            ]
        ]);
    }
}

