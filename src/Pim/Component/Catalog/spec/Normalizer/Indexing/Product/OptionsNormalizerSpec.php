<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing\Product;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Util\ClassUtils;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Normalizer\Indexing\Product\OptionsNormalizer;
use Pim\Component\Catalog\ProductValue\OptionProductValue;
use Pim\Component\Catalog\ProductValue\OptionsProductValue;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Spec for options product value normalizer
 *
 * @author    Anaël Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
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
        OptionsProductValue $optionsValue,
        ProductValueInterface $textValue,
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

    function it_normalize_an_options_product_value_with_no_locale_and_no_channel(
        OptionsProductValue $optionsValue,
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
                            'tagB'
                        ],
                    ],
                ],
            ]
        );
    }

    function it_normalizes_an_option_product_value_with_locale(
        OptionsProductValue $optionsValue,
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
                            'tagB'
                        ],
                    ],
                ],
            ]
        );
    }

    function it_normalizes_an_option_product_value_with_channel(
        OptionsProductValue $optionsValue,
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
                            'tagB'
                        ],
                    ],
                ],
            ]
        );
    }

    function it_normalizes_an_option_product_value_with_locale_and_channel(
        OptionsProductValue $optionsValue,
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
                            'tagB'
                        ],
                    ],
                ],
            ]
        );
    }

    function it_throws_exception_if_it_is_not_an_options_product_value(
        OptionProductValue $optionsValue,
        AttributeInterface $optionsAttribute
    ) {
        $optionsValue->getAttribute()->willReturn($optionsAttribute);
        $optionsAttribute->getBackendType()->willReturn('options');

        $optionsValue->getLocale()->willReturn(null);
        $optionsValue->getScope()->willReturn(null);

        $optionsAttribute->getCode()->willReturn('tags');

        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                ClassUtils::getClass($optionsValue->getWrappedObject()),
                OptionsProductValue::class
            )
        )->during('normalize', [$optionsValue->getWrappedObject(), 'indexing', []]);
    }
}