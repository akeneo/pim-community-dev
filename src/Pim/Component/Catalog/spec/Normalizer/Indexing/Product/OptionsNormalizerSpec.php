<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing\Product;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Normalizer\Indexing\ProductValue\OptionsNormalizer;
use Pim\Component\Catalog\Value\OptionsValue;
use Pim\Component\Catalog\Value\OptionsValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class OptionsNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(OptionsNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_support_options_product_value(
        OptionsValueInterface $optionsValue,
        ValueInterface $textValue,
        AttributeInterface $optionAttribute,
        AttributeInterface $textAttribute
    ) {
        $optionsValue->getAttribute()->willReturn($optionAttribute);
        $textValue->getAttribute()->willReturn($textAttribute);

        $optionAttribute->getBackendType()->willReturn('options');
        $textAttribute->getBackendType()->willReturn('text');

        $this->supportsNormalization(new \stdClass(), 'indexing')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);

        $this->supportsNormalization($textValue, 'indexing')->shouldReturn(false);
        $this->supportsNormalization($optionsValue, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($optionsValue, 'indexing')->shouldReturn(true);
    }

    function it_normalize_an_empty_options_product_value(
        OptionsValue $optionsValue,
        AttributeInterface $optionsAttribute
    ) {
        $optionsValue->getAttribute()->willReturn($optionsAttribute);
        $optionsAttribute->getBackendType()->willReturn('options');

        $optionsValue->getLocale()->willReturn(null);
        $optionsValue->getScope()->willReturn(null);

        $optionsAttribute->getCode()->willReturn('tags');

        $optionsValue->getOptionCodes()->willReturn([]);

        $this->normalize($optionsValue, 'indexing')->shouldReturn(
            [
                'tags-options' => [
                    '<all_channels>' => [
                        '<all_locales>' => [],
                    ],
                ],
            ]
        );
    }

    function it_normalize_an_options_product_value_with_no_locale_and_no_channel(
        OptionsValue $optionsValue,
        AttributeInterface $optionsAttribute
    ) {
        $optionsValue->getAttribute()->willReturn($optionsAttribute);
        $optionsAttribute->getBackendType()->willReturn('options');

        $optionsValue->getLocale()->willReturn(null);
        $optionsValue->getScope()->willReturn(null);

        $optionsAttribute->getCode()->willReturn('tags');

        $optionsValue->getOptionCodes()->willReturn(['tagA', 'tagB']);

        $this->normalize($optionsValue, 'indexing')->shouldReturn(
            [
                'tags-options' => [
                    '<all_channels>' => [
                        '<all_locales>' => [
                            'tagA',
                            'tagB',
                        ],
                    ],
                ],
            ]
        );
    }

    function it_normalizes_an_option_product_value_with_locale(
        OptionsValue $optionsValue,
        AttributeInterface $optionsAttribute
    ) {
        $optionsValue->getAttribute()->willReturn($optionsAttribute);
        $optionsAttribute->getBackendType()->willReturn('options');

        $optionsValue->getLocale()->willReturn('en_US');
        $optionsValue->getScope()->willReturn(null);

        $optionsAttribute->getCode()->willReturn('tags');

        $optionsValue->getOptionCodes()->willReturn(['tagA', 'tagB']);

        $this->normalize($optionsValue, 'indexing')->shouldReturn(
            [
                'tags-options' => [
                    '<all_channels>' => [
                        'en_US' => [
                            'tagA',
                            'tagB',
                        ],
                    ],
                ],
            ]
        );
    }

    function it_normalizes_an_option_product_value_with_channel(
        OptionsValue $optionsValue,
        AttributeInterface $optionsAttribute
    ) {
        $optionsValue->getAttribute()->willReturn($optionsAttribute);
        $optionsAttribute->getBackendType()->willReturn('options');

        $optionsValue->getLocale()->willReturn(null);
        $optionsValue->getScope()->willReturn('ecommerce');

        $optionsAttribute->getCode()->willReturn('tags');

        $optionsValue->getOptionCodes()->willReturn(['tagA', 'tagB']);

        $this->normalize($optionsValue, 'indexing')->shouldReturn(
            [
                'tags-options' => [
                    'ecommerce' => [
                        '<all_locales>' => [
                            'tagA',
                            'tagB',
                        ],
                    ],
                ],
            ]
        );
    }

    function it_normalizes_an_option_product_value_with_locale_and_channel(
        OptionsValue $optionsValue,
        AttributeInterface $optionsAttribute
    ) {
        $optionsValue->getAttribute()->willReturn($optionsAttribute);
        $optionsAttribute->getBackendType()->willReturn('options');

        $optionsValue->getLocale()->willReturn('en_US');
        $optionsValue->getScope()->willReturn('ecommerce');

        $optionsAttribute->getCode()->willReturn('tags');

        $optionsValue->getOptionCodes()->willReturn(['tagA', 'tagB']);

        $this->normalize($optionsValue, 'indexing')->shouldReturn(
            [
                'tags-options' => [
                    'ecommerce' => [
                        'en_US' => [
                            'tagA',
                            'tagB',
                        ],
                    ],
                ],
            ]
        );
    }
}
