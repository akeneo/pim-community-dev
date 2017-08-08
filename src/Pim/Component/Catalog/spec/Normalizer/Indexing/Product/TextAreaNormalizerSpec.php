<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing\Product;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Normalizer\Indexing\ProductValue\TextAreaNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TextAreaNormalizerSpec extends ObjectBehavior
{
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
        AttributeInterface $textAreaAttribute
    ) {
        $textAreaValue->getAttribute()->willReturn($textAreaAttribute);
        $numberValue->getAttribute()->willReturn($numberAttribute);

        $textAreaAttribute->getBackendType()->willReturn('textarea');
        $numberAttribute->getBackendType()->willReturn('decimal');

        $this->supportsNormalization(new \stdClass(), 'indexing')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);

        $this->supportsNormalization($textAreaValue, 'indexing')->shouldReturn(true);
        $this->supportsNormalization($numberValue, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($numberValue, 'indexing')->shouldReturn(false);
    }

    function it_normalizes_a_simple_text_area(
        ValueInterface $textAreaValue,
        AttributeInterface $textAreaAttribute
    ) {
        $textAreaValue->getAttribute()->willReturn($textAreaAttribute);
        $textAreaValue->getLocale()->willReturn(null);
        $textAreaValue->getScope()->willReturn(null);
        $textAreaValue->getData()->willReturn('a product description');

        $textAreaAttribute->getCode()->willReturn('description');
        $textAreaAttribute->getBackendType()->willReturn('textarea');

        $this->normalize($textAreaValue, 'indexing')->shouldReturn([
            'description-textarea' => [
                '<all_channels>' => [
                    '<all_locales>' => 'a product description'
                ]
            ]
        ]);
    }

    function it_normalizes_an_empty_simple_text_area(
        ValueInterface $textAreaValue,
        AttributeInterface $textAreaAttribute
    ) {
        $textAreaValue->getAttribute()->willReturn($textAreaAttribute);
        $textAreaValue->getLocale()->willReturn(null);
        $textAreaValue->getScope()->willReturn(null);
        $textAreaValue->getData()->willReturn(null);

        $textAreaAttribute->getCode()->willReturn('description');
        $textAreaAttribute->getBackendType()->willReturn('textarea');

        $this->normalize($textAreaValue, 'indexing')->shouldReturn([
            'description-textarea' => [
                '<all_channels>' => [
                    '<all_locales>' => null
                ]
            ]
        ]);
    }

    function it_normalizes_a_text_area_with_new_lines(
        ValueInterface $textAreaValue,
        AttributeInterface $textAreaAttribute
    ) {
        $textAreaValue->getAttribute()->willReturn($textAreaAttribute);
        $textAreaValue->getLocale()->willReturn(null);
        $textAreaValue->getScope()->willReturn(null);
        $textAreaValue->getData()->willReturn("a\n product \n\r\n\n
description\r\n");

        $textAreaAttribute->getCode()->willReturn('description');
        $textAreaAttribute->getBackendType()->willReturn('textarea');

        $this->normalize($textAreaValue, 'indexing')->shouldReturn([
            'description-textarea' => [
                '<all_channels>' => [
                    '<all_locales>' => 'a product description'
                ]
            ]
        ]);
    }

    function it_normalizes_a_text_area_with_html_tags(
        ValueInterface $textAreaValue,
        AttributeInterface $textAreaAttribute
    ) {
        $textAreaValue->getAttribute()->willReturn($textAreaAttribute);
        $textAreaValue->getLocale()->willReturn(null);
        $textAreaValue->getScope()->willReturn(null);
        $textAreaValue->getData()->willReturn('<br/><h1>a</h1> <i>product</i><br/> description<hr/><br/>');

        $textAreaAttribute->getCode()->willReturn('description');
        $textAreaAttribute->getBackendType()->willReturn('textarea');

        $this->normalize($textAreaValue, 'indexing')->shouldReturn([
            'description-textarea' => [
                '<all_channels>' => [
                    '<all_locales>' => 'a product description'
                ]
            ]
        ]);
    }

    function it_normalizes_a_text_area_with_html_tags_and_new_lines(
        ValueInterface $textAreaValue,
        AttributeInterface $textAreaAttribute
    ) {
        $textAreaValue->getAttribute()->willReturn($textAreaAttribute);
        $textAreaValue->getLocale()->willReturn(null);
        $textAreaValue->getScope()->willReturn(null);
        $textAreaValue->getData()->willReturn("<br/>\n<h1>a</h1>\r\n <i>product</i>
<br/>\n description<hr/><br/>\n");

        $textAreaAttribute->getCode()->willReturn('description');
        $textAreaAttribute->getBackendType()->willReturn('textarea');

        $this->normalize($textAreaValue, 'indexing')->shouldReturn([
            'description-textarea' => [
                '<all_channels>' => [
                    '<all_locales>' => 'a product description'
                ]
            ]
        ]);
    }

    function it_normalizes_a_text_area_product_value_with_locale_and_no_scope(
        ValueInterface $textAreaValue,
        AttributeInterface $textAreaAttribute
    ) {
        $textAreaValue->getAttribute()->willReturn($textAreaAttribute);
        $textAreaValue->getLocale()->willReturn('fr_FR');
        $textAreaValue->getScope()->willReturn(null);
        $textAreaValue->getData()->willReturn("<h1>a product description</h1>\n");

        $textAreaAttribute->getCode()->willReturn('description');
        $textAreaAttribute->getBackendType()->willReturn('textarea');

        $this->normalize($textAreaValue, 'indexing')->shouldReturn([
            'description-textarea' => [
                '<all_channels>' => [
                    'fr_FR' => 'a product description'
                ]
            ]
        ]);
    }

    function it_normalizes_a_text_area_product_value_with_no_scope_and_no_locale(
        ValueInterface $textAreaValue,
        AttributeInterface $textAreaAttribute
    ) {
        $textAreaValue->getAttribute()->willReturn($textAreaAttribute);
        $textAreaValue->getLocale()->willReturn(null);
        $textAreaValue->getScope()->willReturn('ecommerce');
        $textAreaValue->getData()->willReturn("<h1>a product description</h1>\n");

        $textAreaAttribute->getCode()->willReturn('description');
        $textAreaAttribute->getBackendType()->willReturn('textarea');

        $this->normalize($textAreaValue, 'indexing')->shouldReturn([
            'description-textarea' => [
                'ecommerce' => [
                    '<all_locales>' => 'a product description'
                ]
            ]
        ]);
    }

    function it_normalizes_a_text_area_product_value_with_locale_and_scope(
        ValueInterface $textAreaValue,
        AttributeInterface $textAreaAttribute
    ) {
        $textAreaValue->getAttribute()->willReturn($textAreaAttribute);
        $textAreaValue->getLocale()->willReturn('fr_FR');
        $textAreaValue->getScope()->willReturn('ecommerce');
        $textAreaValue->getData()->willReturn("<h1>a product description</h1>\n");

        $textAreaAttribute->getCode()->willReturn('description');
        $textAreaAttribute->getBackendType()->willReturn('textarea');

        $this->normalize($textAreaValue, 'indexing')->shouldReturn([
            'description-textarea' => [
                'ecommerce' => [
                    'fr_FR' => 'a product description'
                ]
            ]
        ]);
    }
}
