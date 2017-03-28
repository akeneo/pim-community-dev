<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing\Product;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Normalizer\Indexing\Product\TextAreaNormalizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
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
        ProductValueInterface $numberValue,
        ProductValueInterface $textAreaValue,
        AttributeInterface $numberAttribute,
        AttributeInterface $textAreaAttribute
    ) {
        $textAreaValue->getAttribute()->willReturn($textAreaAttribute);
        $numberValue->getAttribute()->willReturn($numberAttribute);

        $textAreaAttribute->getBackendType()->willReturn('text');
        $numberAttribute->getBackendType()->willReturn('decimal');

        $this->supportsNormalization(new \stdClass(), 'indexing')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);

        $this->supportsNormalization($textAreaValue, 'indexing')->shouldReturn(true);
        $this->supportsNormalization($numberValue, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($numberValue, 'indexing')->shouldReturn(false);
    }

    function it_normalizes_a_simple_text_area(
        ProductValueInterface $textAreaValue,
        AttributeInterface $textAreaAttribute
    ) {
        $textAreaValue->getAttribute()->willReturn($textAreaAttribute);
        $textAreaValue->getLocale()->willReturn(null);
        $textAreaValue->getScope()->willReturn(null);
        $textAreaValue->getData()->willReturn('a product name');

        $textAreaAttribute->getCode()->willReturn('name');
        $textAreaAttribute->getBackendType()->willReturn('text');

        $this->normalize($textAreaValue, 'indexing')->shouldReturn([
            'name-text' => [
                '<all_locales>' => [
                    '<all_channels>' => 'a product name'
                ]
            ]
        ]);
    }

    function it_normalizes_a_text_area_with_new_lines(
        ProductValueInterface $textAreaValue,
        AttributeInterface $textAreaAttribute
    ) {
        $textAreaValue->getAttribute()->willReturn($textAreaAttribute);
        $textAreaValue->getLocale()->willReturn(null);
        $textAreaValue->getScope()->willReturn(null);
        $textAreaValue->getData()->willReturn("a\n product \n\r\n\n
name\r\n");

        $textAreaAttribute->getCode()->willReturn('name');
        $textAreaAttribute->getBackendType()->willReturn('text');

        $this->normalize($textAreaValue, 'indexing')->shouldReturn([
            'name-text' => [
                '<all_locales>' => [
                    '<all_channels>' => 'a product name'
                ]
            ]
        ]);
    }

    function it_normalizes_a_text_area_with_html_tags(
        ProductValueInterface $textAreaValue,
        AttributeInterface $textAreaAttribute
    ) {
        $textAreaValue->getAttribute()->willReturn($textAreaAttribute);
        $textAreaValue->getLocale()->willReturn(null);
        $textAreaValue->getScope()->willReturn(null);
        $textAreaValue->getData()->willReturn('<br/><h1>a</h1> <i>product</i><br/> name<hr/><br/>');

        $textAreaAttribute->getCode()->willReturn('name');
        $textAreaAttribute->getBackendType()->willReturn('text');

        $this->normalize($textAreaValue, 'indexing')->shouldReturn([
            'name-text' => [
                '<all_locales>' => [
                    '<all_channels>' => 'a product name'
                ]
            ]
        ]);
    }

    function it_normalizes_a_text_area_with_html_tags_and_new_lines(
        ProductValueInterface $textAreaValue,
        AttributeInterface $textAreaAttribute
    ) {
        $textAreaValue->getAttribute()->willReturn($textAreaAttribute);
        $textAreaValue->getLocale()->willReturn(null);
        $textAreaValue->getScope()->willReturn(null);
        $textAreaValue->getData()->willReturn("<br/>\n<h1>a</h1>\r\n <i>product</i>
<br/>\n name<hr/><br/>\n");

        $textAreaAttribute->getCode()->willReturn('name');
        $textAreaAttribute->getBackendType()->willReturn('text');

        $this->normalize($textAreaValue, 'indexing')->shouldReturn([
            'name-text' => [
                '<all_locales>' => [
                    '<all_channels>' => 'a product name'
                ]
            ]
        ]);
    }

    function it_normalizes_a_text_area_product_value_with_locale_and_no_scope(
        ProductValueInterface $textAreaValue,
        AttributeInterface $textAreaAttribute
    ) {
        $textAreaValue->getAttribute()->willReturn($textAreaAttribute);
        $textAreaValue->getLocale()->willReturn('fr_FR');
        $textAreaValue->getScope()->willReturn(null);
        $textAreaValue->getData()->willReturn("<h1>a product name</h1>\n");

        $textAreaAttribute->getCode()->willReturn('name');
        $textAreaAttribute->getBackendType()->willReturn('text');

        $this->normalize($textAreaValue, 'indexing')->shouldReturn([
            'name-text' => [
                'fr_FR' => [
                    '<all_channels>' => 'a product name'
                ]
            ]
        ]);
    }

    function it_normalizes_a_text_area_product_value_with_no_scope_and_no_locale(
        ProductValueInterface $textAreaValue,
        AttributeInterface $textAreaAttribute
    ) {
        $textAreaValue->getAttribute()->willReturn($textAreaAttribute);
        $textAreaValue->getLocale()->willReturn(null);
        $textAreaValue->getScope()->willReturn('ecommerce');
        $textAreaValue->getData()->willReturn("<h1>a product name</h1>\n");

        $textAreaAttribute->getCode()->willReturn('name');
        $textAreaAttribute->getBackendType()->willReturn('text');

        $this->normalize($textAreaValue, 'indexing')->shouldReturn([
            'name-text' => [
                '<all_locales>' => [
                    'ecommerce' => 'a product name'
                ]
            ]
        ]);
    }

    function it_normalizes_a_text_area_product_value_with_locale_and_scope(
        ProductValueInterface $textAreaValue,
        AttributeInterface $textAreaAttribute
    ) {
        $textAreaValue->getAttribute()->willReturn($textAreaAttribute);
        $textAreaValue->getLocale()->willReturn('fr_FR');
        $textAreaValue->getScope()->willReturn('ecommerce');
        $textAreaValue->getData()->willReturn("<h1>a product name</h1>\n");

        $textAreaAttribute->getCode()->willReturn('name');
        $textAreaAttribute->getBackendType()->willReturn('text');

        $this->normalize($textAreaValue, 'indexing')->shouldReturn([
            'name-text' => [
                'fr_FR' => [
                    'ecommerce' => 'a product name'
                ]
            ]
        ]);
    }
}
