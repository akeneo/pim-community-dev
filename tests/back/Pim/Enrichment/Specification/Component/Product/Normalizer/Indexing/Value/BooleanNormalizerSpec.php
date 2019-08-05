<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\BooleanNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class BooleanNormalizerSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(BooleanNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_support_boolean_product_value_for_both_indexing_formats(
        ValueInterface $textValue,
        ValueInterface $booleanValue,
        AttributeInterface $textAttribute,
        AttributeInterface $booleanAttribute,
        $attributeRepository
    ) {
        $textValue->getAttributeCode()->willReturn('my_text_attribute');
        $attributeRepository->findOneByIdentifier('my_text_attribute')->willReturn($textAttribute);
        $textAttribute->getBackendType()->willReturn('text');

        $booleanValue->getAttributeCode()->willReturn('my_boolean_attribute');
        $attributeRepository->findOneByIdentifier('my_boolean_attribute')->willReturn($booleanAttribute);
        $booleanAttribute->getBackendType()->willReturn('boolean');

        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization($textValue, 'whatever')->shouldReturn(false);

        $this->supportsNormalization(new \stdClass(), ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($booleanValue, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(true);
        $this->supportsNormalization($textValue, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
    }

    function it_normalizes_a_boolean_product_value_with_no_locale_and_no_channel(
        ValueInterface $value,
        AttributeInterface $attribute,
        $attributeRepository
    ) {
        $value->getAttributeCode()->willReturn('a_yes_no');
        $attributeRepository->findOneByIdentifier('a_yes_no')->willReturn($attribute);

        $value->getLocaleCode()->willReturn(null);
        $value->getScopeCode()->willReturn(null);
        $value->getData()->willReturn(true);

        $attribute->getCode()->willReturn('a_yes_no');
        $attribute->getBackendType()->willReturn('boolean');

        $this->normalize($value, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'a_yes_no-boolean' => [
                '<all_channels>' => [
                    '<all_locales>' => true
                ]
            ]
        ]);
    }

    function it_normalizes_a_boolean_product_value_with_locale_and_no_scope(
        ValueInterface $value,
        AttributeInterface $attribute,
        $attributeRepository
    ) {
        $value->getAttributeCode()->willReturn('a_yes_no');
        $attributeRepository->findOneByIdentifier('a_yes_no')->willReturn($attribute);

        $value->getLocaleCode()->willReturn('fr_FR');
        $value->getScopeCode()->willReturn(null);
        $value->getData()->willReturn(true);

        $attribute->getCode()->willReturn('a_yes_no');
        $attribute->getBackendType()->willReturn('boolean');

        $this->normalize($value, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'a_yes_no-boolean' => [
                '<all_channels>' => [
                    'fr_FR' => true
                ]
            ]
        ]);
    }

    function it_normalizes_a_boolean_product_value_with_scope_and_no_locale(
        ValueInterface $value,
        AttributeInterface $attribute,
        $attributeRepository
    ) {
        $value->getAttributeCode()->willReturn('a_yes_no');
        $attributeRepository->findOneByIdentifier('a_yes_no')->willReturn($attribute);

        $value->getLocaleCode()->willReturn(null);
        $value->getScopeCode()->willReturn('ecommerce');
        $value->getData()->willReturn(true);

        $attribute->getCode()->willReturn('a_yes_no');
        $attribute->getBackendType()->willReturn('boolean');

        $this->normalize($value, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'a_yes_no-boolean' => [
                'ecommerce' => [
                    '<all_locales>' => true
                ]
            ]
        ]);
    }

    function it_normalizes_a_boolean_product_value_with_locale_and_scope(
        ValueInterface $value,
        AttributeInterface $attribute,
        $attributeRepository
    ) {
        $value->getAttributeCode()->willReturn('a_yes_no');
        $attributeRepository->findOneByIdentifier('a_yes_no')->willReturn($attribute);

        $value->getLocaleCode()->willReturn('fr_FR');
        $value->getScopeCode()->willReturn('ecommerce');
        $value->getData()->willReturn(true);

        $attribute->getCode()->willReturn('a_yes_no');
        $attribute->getBackendType()->willReturn('boolean');

        $this->normalize($value, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'a_yes_no-boolean' => [
                'ecommerce' => [
                    'fr_FR' => true
                ]
            ]
        ]);
    }

    function it_normalizes_an_empty_boolean_product_value(
        ValueInterface $value,
        AttributeInterface $attribute,
        $attributeRepository
    ) {
        $value->getAttributeCode()->willReturn('a_yes_no');
        $attributeRepository->findOneByIdentifier('a_yes_no')->willReturn($attribute);

        $value->getLocaleCode()->willReturn(null);
        $value->getScopeCode()->willReturn(null);
        $value->getData()->willReturn(null);

        $attribute->getCode()->willReturn('a_yes_no');
        $attribute->getBackendType()->willReturn('boolean');

        $this->normalize($value, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'a_yes_no-boolean' => [
                '<all_channels>' => [
                    '<all_locales>' => null
                ]
            ]
        ]);
    }
}
