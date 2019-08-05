<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\TextNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TextNormalizerSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TextNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_text_product_value(
        ValueInterface $numberValue,
        ValueInterface $textValue,
        AttributeInterface $numberAttribute,
        AttributeInterface $textAttribute,
        $attributeRepository
    ) {
        $textValue->getAttributeCode()->willReturn('my_text_attribute');
        $numberValue->getAttributeCode()->willReturn('my_number_attribute');

        $textAttribute->getBackendType()->willReturn('text');
        $numberAttribute->getBackendType()->willReturn('decimal');

        $attributeRepository->findOneByIdentifier('my_text_attribute')->willReturn($textAttribute);
        $attributeRepository->findOneByIdentifier('my_number_attribute')->willReturn($numberAttribute);

        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);

        $this->supportsNormalization($numberValue, 'whatever')->shouldReturn(false);

        $this->supportsNormalization(new \stdClass(), ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($textValue, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(true);
        $this->supportsNormalization($numberValue, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
    }

    function it_normalizes_a_text_product_value_with_no_locale_and_no_channel(
        ValueInterface $textValue,
        AttributeInterface $textAttribute,
        $attributeRepository
    ) {
        $textValue->getAttributeCode()->willReturn('name');
        $textValue->getLocaleCode()->willReturn(null);
        $textValue->getScopeCode()->willReturn(null);
        $textValue->getData()->willReturn('a product name');

        $textAttribute->getCode()->willReturn('name');
        $textAttribute->getBackendType()->willReturn('text');
        $attributeRepository->findOneByIdentifier('name')->willReturn($textAttribute);

        $this->normalize($textValue, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'name-text' => [
                '<all_channels>' => [
                    '<all_locales>' => 'a product name',
                ],
            ],
        ]);
    }

    function it_keeps_the_string_as_is_during_normalization(
        ValueInterface $textValue,
        AttributeInterface $textAttribute,
        $attributeRepository
    ) {
        $textValue->getAttributeCode()->willReturn('name');
        $textValue->getLocaleCode()->willReturn(null);
        $textValue->getScopeCode()->willReturn(null);
        $textValue->getData()->willReturn('<h1>My <strong>ProDucT</strong> is awesome</h1>');

        $textAttribute->getCode()->willReturn('name');
        $textAttribute->getBackendType()->willReturn('text');
        $attributeRepository->findOneByIdentifier('name')->willReturn($textAttribute);

        $this->normalize($textValue, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'name-text' => [
                '<all_channels>' => [
                    '<all_locales>' => '<h1>My <strong>ProDucT</strong> is awesome</h1>',
                ],
            ],
        ]);
    }

    function it_normalizes_an_empty_text_with_no_locale_and_channel(
        ValueInterface $textValue,
        AttributeInterface $textAttribute,
        $attributeRepository
    ) {
        $textValue->getAttributeCode()->willReturn('name');
        $textValue->getLocaleCode()->willReturn(null);
        $textValue->getScopeCode()->willReturn(null);
        $textValue->getData()->willReturn(null);

        $textAttribute->getCode()->willReturn('name');
        $textAttribute->getBackendType()->willReturn('text');
        $attributeRepository->findOneByIdentifier('name')->willReturn($textAttribute);

        $this->normalize($textValue, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'name-text' => [
                '<all_channels>' => [
                    '<all_locales>' => null,
                ],
            ],
        ]);
    }

    function it_normalizes_a_text_product_value_with_locale_and_no_scope(
        ValueInterface $textValue,
        AttributeInterface $textAttribute,
        $attributeRepository
    ) {
        $textValue->getAttributeCode()->willReturn('name');
        $textValue->getLocaleCode()->willReturn('fr_FR');
        $textValue->getScopeCode()->willReturn(null);
        $textValue->getData()->willReturn('a product name');

        $textAttribute->getCode()->willReturn('name');
        $textAttribute->getBackendType()->willReturn('text');
        $attributeRepository->findOneByIdentifier('name')->willReturn($textAttribute);

        $this->normalize($textValue, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'name-text' => [
                '<all_channels>' => [
                    'fr_FR' => 'a product name',
                ],
            ],
        ]);
    }

    function it_normalizes_a_text_product_value_with_scope_and_no_locale(
        ValueInterface $textValue,
        AttributeInterface $textAttribute,
        $attributeRepository
    ) {
        $textValue->getAttributeCode()->willReturn('name');
        $textValue->getLocaleCode()->willReturn(null);
        $textValue->getScopeCode()->willReturn('ecommerce');
        $textValue->getData()->willReturn('a product name');

        $textAttribute->getCode()->willReturn('name');
        $textAttribute->getBackendType()->willReturn('text');
        $attributeRepository->findOneByIdentifier('name')->willReturn($textAttribute);

        $this->normalize($textValue, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'name-text' => [
                'ecommerce' => [
                    '<all_locales>' => 'a product name',
                ],
            ],
        ]);
    }

    function it_normalizes_a_text_product_value_with_locale_and_scope(
        ValueInterface $textValue,
        AttributeInterface $textAttribute,
        $attributeRepository
    ) {
        $textValue->getAttributeCode()->willReturn('name');
        $textValue->getLocaleCode()->willReturn('fr_FR');
        $textValue->getScopeCode()->willReturn('ecommerce');
        $textValue->getData()->willReturn('a product name');

        $textAttribute->getCode()->willReturn('name');
        $textAttribute->getBackendType()->willReturn('text');
        $attributeRepository->findOneByIdentifier('name')->willReturn($textAttribute);

        $this->normalize($textValue, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'name-text' => [
                'ecommerce' => [
                    'fr_FR' => 'a product name',
                ],
            ],
        ]);
    }
}
