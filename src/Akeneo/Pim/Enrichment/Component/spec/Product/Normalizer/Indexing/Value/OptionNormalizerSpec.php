<?php

namespace spec\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Product\ProductNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\OptionNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValueInterface;
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

        $this->supportsNormalization(new \stdClass(), ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);

        $this->supportsNormalization($textValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn(false);
        $this->supportsNormalization($optionValue, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($optionValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->shouldReturn(true);

        $this->supportsNormalization(new \stdClass(), ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($textValue, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($optionValue, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(true);
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

        $this->normalize($optionValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn(
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

        $this->normalize($optionValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn(
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

        $this->normalize($optionValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn(
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

        $this->normalize($optionValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn(
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

        $this->normalize($optionValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn(
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
