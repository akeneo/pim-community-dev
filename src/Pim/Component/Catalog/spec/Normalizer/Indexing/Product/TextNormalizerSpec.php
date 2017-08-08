<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing\Product;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Normalizer\Indexing\ProductValue\TextNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TextNormalizerSpec extends ObjectBehavior
{
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
        AttributeInterface $textAttribute
    ) {
        $textValue->getAttribute()->willReturn($textAttribute);
        $numberValue->getAttribute()->willReturn($numberAttribute);

        $textAttribute->getBackendType()->willReturn('text');
        $numberAttribute->getBackendType()->willReturn('decimal');

        $this->supportsNormalization(new \stdClass(), 'indexing')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);

        $this->supportsNormalization($textValue, 'indexing')->shouldReturn(true);
        $this->supportsNormalization($numberValue, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($numberValue, 'indexing')->shouldReturn(false);
    }

    function it_normalizes_a_text_product_value_with_no_locale_and_no_channel(
        ValueInterface $textValue,
        AttributeInterface $textAttribute
    ) {
        $textValue->getAttribute()->willReturn($textAttribute);
        $textValue->getLocale()->willReturn(null);
        $textValue->getScope()->willReturn(null);
        $textValue->getData()->willReturn('a product name');

        $textAttribute->getCode()->willReturn('name');
        $textAttribute->getBackendType()->willReturn('text');

        $this->normalize($textValue, 'indexing')->shouldReturn([
            'name-text' => [
                '<all_channels>' => [
                    '<all_locales>' => 'a product name',
                ],
            ],
        ]);
    }

    function it_keeps_the_string_as_is_during_normalization(
        ValueInterface $textValue,
        AttributeInterface $textAttribute
    ) {
        $textValue->getAttribute()->willReturn($textAttribute);
        $textValue->getLocale()->willReturn(null);
        $textValue->getScope()->willReturn(null);
        $textValue->getData()->willReturn('<h1>My <strong>ProDucT</strong> is awesome</h1>');

        $textAttribute->getCode()->willReturn('name');
        $textAttribute->getBackendType()->willReturn('text');

        $this->normalize($textValue, 'indexing')->shouldReturn([
            'name-text' => [
                '<all_channels>' => [
                    '<all_locales>' => '<h1>My <strong>ProDucT</strong> is awesome</h1>',
                ],
            ],
        ]);
    }

    function it_normalizes_an_empty_text_with_no_locale_and_channel(
        ValueInterface $textValue,
        AttributeInterface $textAttribute
    ) {
        $textValue->getAttribute()->willReturn($textAttribute);
        $textValue->getLocale()->willReturn(null);
        $textValue->getScope()->willReturn(null);
        $textValue->getData()->willReturn(null);

        $textAttribute->getCode()->willReturn('name');
        $textAttribute->getBackendType()->willReturn('text');

        $this->normalize($textValue, 'indexing')->shouldReturn([
            'name-text' => [
                '<all_channels>' => [
                    '<all_locales>' => null,
                ],
            ],
        ]);
    }

    function it_normalizes_a_text_product_value_with_locale_and_no_scope(
        ValueInterface $textValue,
        AttributeInterface $textAttribute
    ) {
        $textValue->getAttribute()->willReturn($textAttribute);
        $textValue->getLocale()->willReturn('fr_FR');
        $textValue->getScope()->willReturn(null);
        $textValue->getData()->willReturn('a product name');

        $textAttribute->getCode()->willReturn('name');
        $textAttribute->getBackendType()->willReturn('text');

        $this->normalize($textValue, 'indexing')->shouldReturn([
            'name-text' => [
                '<all_channels>' => [
                    'fr_FR' => 'a product name',
                ],
            ],
        ]);
    }

    function it_normalizes_a_text_product_value_with_scope_and_no_locale(
        ValueInterface $textValue,
        AttributeInterface $textAttribute
    ) {
        $textValue->getAttribute()->willReturn($textAttribute);
        $textValue->getLocale()->willReturn(null);
        $textValue->getScope()->willReturn('ecommerce');
        $textValue->getData()->willReturn('a product name');

        $textAttribute->getCode()->willReturn('name');
        $textAttribute->getBackendType()->willReturn('text');

        $this->normalize($textValue, 'indexing')->shouldReturn([
            'name-text' => [
                'ecommerce' => [
                    '<all_locales>' => 'a product name',
                ],
            ],
        ]);
    }

    function it_normalizes_a_text_product_value_with_locale_and_scope(
        ValueInterface $textValue,
        AttributeInterface $textAttribute
    ) {
        $textValue->getAttribute()->willReturn($textAttribute);
        $textValue->getLocale()->willReturn('fr_FR');
        $textValue->getScope()->willReturn('ecommerce');
        $textValue->getData()->willReturn('a product name');

        $textAttribute->getCode()->willReturn('name');
        $textAttribute->getBackendType()->willReturn('text');

        $this->normalize($textValue, 'indexing')->shouldReturn([
            'name-text' => [
                'ecommerce' => [
                    'fr_FR' => 'a product name',
                ],
            ],
        ]);
    }
}
