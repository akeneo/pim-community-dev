<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\TextAreaNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TextAreaNormalizerSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TextAreaNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_text_area_product_value(
        ValueInterface $numberValue,
        ValueInterface $textAreaValue,
        AttributeInterface $numberAttribute,
        AttributeInterface $textAreaAttribute,
        $attributeRepository
    ) {
        $textAreaValue->getAttributeCode()->willReturn('my_textarea_attribute');
        $numberValue->getAttributeCode()->willReturn('my_number_attribute');

        $textAreaAttribute->getBackendType()->willReturn('textarea');
        $numberAttribute->getBackendType()->willReturn('decimal');

        $attributeRepository->findOneByIdentifier('my_textarea_attribute')->willReturn($textAreaAttribute);
        $attributeRepository->findOneByIdentifier('my_number_attribute')->willReturn($numberAttribute);

        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization($numberValue, 'whatever')->shouldReturn(false);

        $this->supportsNormalization(new \stdClass(), ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($textAreaValue, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(true);
        $this->supportsNormalization($numberValue, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
    }

    function it_normalizes_a_simple_text_area(
        ValueInterface $textAreaValue,
        AttributeInterface $textAreaAttribute,
        $attributeRepository
    ) {
        $textAreaValue->getAttributeCode()->willReturn('description');
        $textAreaValue->getLocaleCode()->willReturn(null);
        $textAreaValue->getScopeCode()->willReturn(null);
        $textAreaValue->getData()->willReturn('a product description');

        $textAreaAttribute->getCode()->willReturn('description');
        $textAreaAttribute->getBackendType()->willReturn('textarea');
        $attributeRepository->findOneByIdentifier('description')->willReturn($textAreaAttribute);

        $this->normalize($textAreaValue, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'description-textarea' => [
                '<all_channels>' => [
                    '<all_locales>' => 'a product description'
                ]
            ]
        ]);
    }

    function it_normalizes_an_empty_simple_text_area(
        ValueInterface $textAreaValue,
        AttributeInterface $textAreaAttribute,
        $attributeRepository
    ) {
        $textAreaValue->getAttributeCode()->willReturn('description');
        $textAreaValue->getLocaleCode()->willReturn(null);
        $textAreaValue->getScopeCode()->willReturn(null);
        $textAreaValue->getData()->willReturn(null);

        $textAreaAttribute->getCode()->willReturn('description');
        $textAreaAttribute->getBackendType()->willReturn('textarea');
        $attributeRepository->findOneByIdentifier('description')->willReturn($textAreaAttribute);

        $this->normalize($textAreaValue, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'description-textarea' => [
                '<all_channels>' => [
                    '<all_locales>' => null
                ]
            ]
        ]);
    }

    function it_normalizes_a_text_area_with_new_lines(
        ValueInterface $textAreaValue,
        AttributeInterface $textAreaAttribute,
        $attributeRepository
    ) {
        $textAreaValue->getAttributeCode()->willReturn('description');
        $textAreaValue->getLocaleCode()->willReturn(null);
        $textAreaValue->getScopeCode()->willReturn(null);
        $textAreaValue->getData()->willReturn("a\n product \n\r\n\n
description\r\n");

        $textAreaAttribute->getCode()->willReturn('description');
        $textAreaAttribute->getBackendType()->willReturn('textarea');
        $attributeRepository->findOneByIdentifier('description')->willReturn($textAreaAttribute);

        $this->normalize($textAreaValue, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'description-textarea' => [
                '<all_channels>' => [
                    '<all_locales>' => 'a product description'
                ]
            ]
        ]);
    }

    function it_normalizes_a_text_area_with_html_tags(
        ValueInterface $textAreaValue,
        AttributeInterface $textAreaAttribute,
        $attributeRepository
    ) {
        $textAreaValue->getAttributeCode()->willReturn('description');
        $textAreaValue->getLocaleCode()->willReturn(null);
        $textAreaValue->getScopeCode()->willReturn(null);
        $textAreaValue->getData()->willReturn('<br/><h1>a</h1> <i>product</i><br/> description<hr/><br/>');

        $textAreaAttribute->getCode()->willReturn('description');
        $textAreaAttribute->getBackendType()->willReturn('textarea');
        $attributeRepository->findOneByIdentifier('description')->willReturn($textAreaAttribute);

        $this->normalize($textAreaValue, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'description-textarea' => [
                '<all_channels>' => [
                    '<all_locales>' => 'a product description'
                ]
            ]
        ]);
    }

    function it_normalizes_a_text_area_with_html_tags_and_new_lines(
        ValueInterface $textAreaValue,
        AttributeInterface $textAreaAttribute,
        $attributeRepository
    ) {
        $textAreaValue->getAttributeCode()->willReturn('description');
        $textAreaValue->getLocaleCode()->willReturn(null);
        $textAreaValue->getScopeCode()->willReturn(null);
        $textAreaValue->getData()->willReturn("<br/>\n<h1>a</h1>\r\n <i>product</i>
<br/>\n description<hr/><br/>\n");

        $textAreaAttribute->getCode()->willReturn('description');
        $textAreaAttribute->getBackendType()->willReturn('textarea');
        $attributeRepository->findOneByIdentifier('description')->willReturn($textAreaAttribute);

        $this->normalize($textAreaValue, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'description-textarea' => [
                '<all_channels>' => [
                    '<all_locales>' => 'a product description'
                ]
            ]
        ]);
    }

    function it_normalizes_a_text_area_product_value_with_locale_and_no_scope(
        ValueInterface $textAreaValue,
        AttributeInterface $textAreaAttribute,
        $attributeRepository
    ) {
        $textAreaValue->getAttributeCode()->willReturn('description');
        $textAreaValue->getLocaleCode()->willReturn('fr_FR');
        $textAreaValue->getScopeCode()->willReturn(null);
        $textAreaValue->getData()->willReturn("<h1>a product description</h1>\n");

        $textAreaAttribute->getCode()->willReturn('description');
        $textAreaAttribute->getBackendType()->willReturn('textarea');
        $attributeRepository->findOneByIdentifier('description')->willReturn($textAreaAttribute);

        $this->normalize($textAreaValue, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'description-textarea' => [
                '<all_channels>' => [
                    'fr_FR' => 'a product description'
                ]
            ]
        ]);
    }

    function it_normalizes_a_text_area_product_value_with_no_scope_and_no_locale(
        ValueInterface $textAreaValue,
        AttributeInterface $textAreaAttribute,
        $attributeRepository
    ) {
        $textAreaValue->getAttributeCode()->willReturn('description');
        $textAreaValue->getLocaleCode()->willReturn(null);
        $textAreaValue->getScopeCode()->willReturn('ecommerce');
        $textAreaValue->getData()->willReturn("<h1>a product description</h1>\n");

        $textAreaAttribute->getCode()->willReturn('description');
        $textAreaAttribute->getBackendType()->willReturn('textarea');
        $attributeRepository->findOneByIdentifier('description')->willReturn($textAreaAttribute);

        $this->normalize($textAreaValue, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'description-textarea' => [
                'ecommerce' => [
                    '<all_locales>' => 'a product description'
                ]
            ]
        ]);
    }

    function it_normalizes_a_text_area_product_value_with_locale_and_scope(
        ValueInterface $textAreaValue,
        AttributeInterface $textAreaAttribute,
        $attributeRepository
    ) {
        $textAreaValue->getAttributeCode()->willReturn('description');
        $textAreaValue->getLocaleCode()->willReturn('fr_FR');
        $textAreaValue->getScopeCode()->willReturn('ecommerce');
        $textAreaValue->getData()->willReturn("<h1>a product description</h1>\n");

        $textAreaAttribute->getCode()->willReturn('description');
        $textAreaAttribute->getBackendType()->willReturn('textarea');
        $attributeRepository->findOneByIdentifier('description')->willReturn($textAreaAttribute);

        $this->normalize($textAreaValue, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'description-textarea' => [
                'ecommerce' => [
                    'fr_FR' => 'a product description'
                ]
            ]
        ]);
    }
}
