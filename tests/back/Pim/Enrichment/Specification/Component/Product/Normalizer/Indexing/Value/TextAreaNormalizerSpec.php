<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\TextAreaNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TextAreaNormalizerSpec extends ObjectBehavior
{
    function let(GetAttributes $getAttributes)
    {
        $this->beConstructedWith($getAttributes);
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
        GetAttributes $getAttributes
    ) {
        $textAreaValue->getAttributeCode()->willReturn('my_textarea_attribute');
        $numberValue->getAttributeCode()->willReturn('my_number_attribute');

        $getAttributes->forCode('my_textarea_attribute')->willReturn(new Attribute(
            'my_textarea_attribute',
            'pim_catalog_textarea',
            [],
            false,
            false,
            null,
            true,
            'textarea',
            []
        ));
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

        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization($numberValue, 'whatever')->shouldReturn(false);

        $this->supportsNormalization(new \stdClass(), ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($textAreaValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(true);
        $this->supportsNormalization($numberValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
    }

    function it_normalizes_a_simple_text_area(
        ValueInterface $textAreaValue,
        GetAttributes $getAttributes
    ) {
        $textAreaValue->getAttributeCode()->willReturn('description');
        $textAreaValue->getLocaleCode()->willReturn(null);
        $textAreaValue->getScopeCode()->willReturn(null);
        $textAreaValue->getData()->willReturn('a product description');

        $getAttributes->forCode('description')->willReturn(new Attribute(
            'description',
            'pim_catalog_textarea',
            [],
            false,
            false,
            null,
            true,
            'textarea',
            []
        ));

        $this->normalize($textAreaValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'description-textarea' => [
                '<all_channels>' => [
                    '<all_locales>' => 'a product description'
                ]
            ]
        ]);
    }

    function it_normalizes_an_empty_simple_text_area(
        ValueInterface $textAreaValue,
        GetAttributes $getAttributes
    ) {
        $textAreaValue->getAttributeCode()->willReturn('description');
        $textAreaValue->getLocaleCode()->willReturn(null);
        $textAreaValue->getScopeCode()->willReturn(null);
        $textAreaValue->getData()->willReturn(null);

        $getAttributes->forCode('description')->willReturn(new Attribute(
            'description',
            'pim_catalog_textarea',
            [],
            false,
            false,
            null,
            true,
            'textarea',
            []
        ));

        $this->normalize($textAreaValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'description-textarea' => [
                '<all_channels>' => [
                    '<all_locales>' => null
                ]
            ]
        ]);
    }

    function it_normalizes_a_text_area_with_new_lines(
        ValueInterface $textAreaValue,
        GetAttributes $getAttributes
    ) {
        $textAreaValue->getAttributeCode()->willReturn('description');
        $textAreaValue->getLocaleCode()->willReturn(null);
        $textAreaValue->getScopeCode()->willReturn(null);
        $textAreaValue->getData()->willReturn("a\n product \n\r\n\n
description\r\n");

        $getAttributes->forCode('description')->willReturn(new Attribute(
            'description',
            'pim_catalog_textarea',
            [],
            false,
            false,
            null,
            true,
            'textarea',
            []
        ));

        $this->normalize($textAreaValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'description-textarea' => [
                '<all_channels>' => [
                    '<all_locales>' => 'a product description'
                ]
            ]
        ]);
    }

    function it_normalizes_a_text_area_with_html_tags(
        ValueInterface $textAreaValue,
        GetAttributes $getAttributes
    ) {
        $textAreaValue->getAttributeCode()->willReturn('description');
        $textAreaValue->getLocaleCode()->willReturn(null);
        $textAreaValue->getScopeCode()->willReturn(null);
        $textAreaValue->getData()->willReturn('<br/><h1>a</h1> <i>product</i><br/> description<hr/><br/>');

        $getAttributes->forCode('description')->willReturn(new Attribute(
            'description',
            'pim_catalog_textarea',
            [],
            false,
            false,
            null,
            true,
            'textarea',
            []
        ));

        $this->normalize($textAreaValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'description-textarea' => [
                '<all_channels>' => [
                    '<all_locales>' => 'a product description'
                ]
            ]
        ]);
    }

    function it_normalizes_a_text_area_with_html_tags_and_new_lines(
        ValueInterface $textAreaValue,
        GetAttributes $getAttributes
    ) {
        $textAreaValue->getAttributeCode()->willReturn('description');
        $textAreaValue->getLocaleCode()->willReturn(null);
        $textAreaValue->getScopeCode()->willReturn(null);
        $textAreaValue->getData()->willReturn("<br/>\n<h1>a</h1>\r\n <i>product</i>
<br/>\n description<hr/><br/>\n");

        $getAttributes->forCode('description')->willReturn(new Attribute(
            'description',
            'pim_catalog_textarea',
            [],
            false,
            false,
            null,
            true,
            'textarea',
            []
        ));

        $this->normalize($textAreaValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'description-textarea' => [
                '<all_channels>' => [
                    '<all_locales>' => 'a product description'
                ]
            ]
        ]);
    }

    function it_normalizes_a_text_area_product_value_with_locale_and_no_scope(
        ValueInterface $textAreaValue,
        GetAttributes $getAttributes
    ) {
        $textAreaValue->getAttributeCode()->willReturn('description');
        $textAreaValue->getLocaleCode()->willReturn('fr_FR');
        $textAreaValue->getScopeCode()->willReturn(null);
        $textAreaValue->getData()->willReturn("<h1>a product description</h1>\n");

        $getAttributes->forCode('description')->willReturn(new Attribute(
            'description',
            'pim_catalog_textarea',
            [],
            true,
            false,
            null,
            true,
            'textarea',
            []
        ));

        $this->normalize($textAreaValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'description-textarea' => [
                '<all_channels>' => [
                    'fr_FR' => 'a product description'
                ]
            ]
        ]);
    }

    function it_normalizes_a_text_area_product_value_with_scope_and_no_locale(
        ValueInterface $textAreaValue,
        GetAttributes $getAttributes
    ) {
        $textAreaValue->getAttributeCode()->willReturn('description');
        $textAreaValue->getLocaleCode()->willReturn(null);
        $textAreaValue->getScopeCode()->willReturn('ecommerce');
        $textAreaValue->getData()->willReturn("<h1>a product description</h1>\n");

        $getAttributes->forCode('description')->willReturn(new Attribute(
            'description',
            'pim_catalog_textarea',
            [],
            false,
            true,
            null,
            true,
            'textarea',
            []
        ));

        $this->normalize($textAreaValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'description-textarea' => [
                'ecommerce' => [
                    '<all_locales>' => 'a product description'
                ]
            ]
        ]);
    }

    function it_normalizes_a_text_area_product_value_with_locale_and_scope(
        ValueInterface $textAreaValue,
        GetAttributes $getAttributes
    ) {
        $textAreaValue->getAttributeCode()->willReturn('description');
        $textAreaValue->getLocaleCode()->willReturn('fr_FR');
        $textAreaValue->getScopeCode()->willReturn('ecommerce');
        $textAreaValue->getData()->willReturn("<h1>a product description</h1>\n");

        $getAttributes->forCode('description')->willReturn(new Attribute(
            'description',
            'pim_catalog_textarea',
            [],
            true,
            true,
            null,
            true,
            'textarea',
            []
        ));

        $this->normalize($textAreaValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'description-textarea' => [
                'ecommerce' => [
                    'fr_FR' => 'a product description'
                ]
            ]
        ]);
    }
}
