<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing\Product;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Normalizer\Indexing\ProductValue\OptionNormalizer;
use Pim\Component\Catalog\Value\OptionValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class OptionNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(OptionNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_support_option_product_value(
        OptionValueInterface $optionValue,
        ValueInterface $textValue,
        AttributeInterface $optionAttribute,
        AttributeInterface $textAttribute
    ) {
        $optionValue->getAttribute()->willReturn($optionAttribute);
        $textValue->getAttribute()->willReturn($textAttribute);

        $optionAttribute->getBackendType()->willReturn('option');
        $textAttribute->getBackendType()->willReturn('text');

        $this->supportsNormalization(new \stdClass(), 'indexing')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);

        $this->supportsNormalization($textValue, 'indexing')->shouldReturn(false);
        $this->supportsNormalization($optionValue, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($optionValue, 'indexing')->shouldReturn(true);
    }

    function it_normalize_an_empty_option_product_value(
        ValueInterface $optionValue,
        AttributeInterface $optionAttribute
    ) {
        $optionValue->getAttribute()->willReturn($optionAttribute);
        $optionAttribute->getBackendType()->willReturn('option');

        $optionValue->getLocale()->willReturn(null);
        $optionValue->getScope()->willReturn(null);

        $optionAttribute->getCode()->willReturn('color');

        $optionValue->getData()->willReturn(null);

        $this->normalize($optionValue, 'indexing')->shouldReturn(
            [
                'color-option' => [
                    '<all_channels>' => [
                        '<all_locales>' => null,
                    ],
                ],
            ]
        );
    }

    function it_normalize_an_option_product_value_with_no_locale_and_no_channel(
        ValueInterface $optionValue,
        AttributeInterface $optionAttribute,
        AttributeOptionInterface $color
    ) {
        $optionValue->getAttribute()->willReturn($optionAttribute);
        $optionAttribute->getBackendType()->willReturn('option');

        $optionValue->getLocale()->willReturn(null);
        $optionValue->getScope()->willReturn(null);

        $optionAttribute->getCode()->willReturn('color');

        $optionValue->getData()->willReturn($color);
        $color->getCode()->willReturn('red');

        $this->normalize($optionValue, 'indexing')->shouldReturn(
            [
                'color-option' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'red',
                    ],
                ],
            ]
        );
    }

    function it_normalizes_an_option_product_value_with_locale(
        ValueInterface $optionValue,
        AttributeInterface $optionAttribute,
        AttributeOptionInterface $color
    ) {
        $optionValue->getAttribute()->willReturn($optionAttribute);
        $optionAttribute->getBackendType()->willReturn('option');

        $optionValue->getLocale()->willReturn('en_US');
        $optionValue->getScope()->willReturn(null);

        $optionAttribute->getCode()->willReturn('color');

        $optionValue->getData()->willReturn($color);
        $color->getCode()->willReturn('red');

        $this->normalize($optionValue, 'indexing')->shouldReturn(
            [
                'color-option' => [
                    '<all_channels>' => [
                        'en_US' => 'red',
                    ],
                ],
            ]
        );
    }

    function it_normalizes_an_option_product_value_with_channel(
        ValueInterface $optionValue,
        AttributeInterface $optionAttribute,
        AttributeOptionInterface $color
    ) {
        $optionValue->getAttribute()->willReturn($optionAttribute);
        $optionAttribute->getBackendType()->willReturn('option');

        $optionValue->getLocale()->willReturn(null);
        $optionValue->getScope()->willReturn('ecommerce');

        $optionAttribute->getCode()->willReturn('color');

        $optionValue->getData()->willReturn($color);
        $color->getCode()->willReturn('red');

        $this->normalize($optionValue, 'indexing')->shouldReturn(
            [
                'color-option' => [
                    'ecommerce' => [
                        '<all_locales>' => 'red',
                    ],
                ],
            ]
        );
    }

    function it_normalizes_an_option_product_value_with_locale_and_channel(
        ValueInterface $optionValue,
        AttributeInterface $optionAttribute,
        AttributeOptionInterface $color
    ) {
        $optionValue->getAttribute()->willReturn($optionAttribute);
        $optionAttribute->getBackendType()->willReturn('option');

        $optionValue->getLocale()->willReturn('en_US');
        $optionValue->getScope()->willReturn('ecommerce');

        $optionAttribute->getCode()->willReturn('color');

        $optionValue->getData()->willReturn($color);
        $color->getCode()->willReturn('red');

        $this->normalize($optionValue, 'indexing')->shouldReturn(
            [
                'color-option' => [
                    'ecommerce' => [
                        'en_US' => 'red',
                    ],
                ],
            ]
        );
    }
}
