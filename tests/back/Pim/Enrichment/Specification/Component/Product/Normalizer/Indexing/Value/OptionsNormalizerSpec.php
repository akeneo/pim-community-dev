<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\OptionsNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class OptionsNormalizerSpec extends ObjectBehavior
{
    function let(GetAttributes $getAttributes)
    {
        $this->beConstructedWith($getAttributes);
    }

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
        GetAttributes $getAttributes
    ) {
        $optionsValue->getAttributeCode()->willReturn('my_options_attribute');
        $textValue->getAttributeCode()->willReturn('my_text_attribute');

        $getAttributes->forCode('my_options_attribute')->willReturn(new Attribute(
            'my_options_attribute',
            'pim_catalog_multiselect',
            [],
            false,
            false,
            null,
            true,
            'options',
            []
        ));
        $getAttributes->forCode('my_text_attribute')->willReturn(new Attribute(
            'my_text_attribute',
            'pim_catalog_text',
            [],
            false,
            false,
            null,
            true,
            'text',
            []
        ));

        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization($optionsValue, 'whatever')->shouldReturn(false);

        $this->supportsNormalization(new \stdClass(), ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($textValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($optionsValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(true);
    }

    function it_normalize_an_empty_options_product_value(
        OptionsValue $optionsValue,
        GetAttributes $getAttributes
    ) {
        $optionsValue->getAttributeCode()->willReturn('my_options_attribute');
        $getAttributes->forCode('my_options_attribute')->willReturn(new Attribute(
            'my_options_attribute',
            'pim_catalog_multiselect',
            [],
            false,
            false,
            null,
            true,
            'options',
            []
        ));

        $optionsValue->getLocaleCode()->willReturn(null);
        $optionsValue->getScopeCode()->willReturn(null);

        $optionsValue->getOptionCodes()->willReturn([]);

        $this->normalize($optionsValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'my_options_attribute-options' => [
                    '<all_channels>' => [
                        '<all_locales>' => [],
                    ],
                ],
            ]
        );
    }

    function it_normalize_an_options_product_value_with_no_locale_and_no_channel(
        OptionsValue $optionsValue,
        GetAttributes $getAttributes
    ) {
        $optionsValue->getAttributeCode()->willReturn('my_options_attribute');
        $getAttributes->forCode('my_options_attribute')->willReturn(new Attribute(
            'my_options_attribute',
            'pim_catalog_multiselect',
            [],
            false,
            false,
            null,
            true,
            'options',
            []
        ));

        $optionsValue->getLocaleCode()->willReturn(null);
        $optionsValue->getScopeCode()->willReturn(null);

        $optionsValue->getOptionCodes()->willReturn(['tagA', 'tagB']);

        $this->normalize($optionsValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'my_options_attribute-options' => [
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
        GetAttributes $getAttributes
    ) {
        $optionsValue->getAttributeCode()->willReturn('my_options_attribute');
        $getAttributes->forCode('my_options_attribute')->willReturn(new Attribute(
            'my_options_attribute',
            'pim_catalog_multiselect',
            [],
            false,
            false,
            null,
            true,
            'options',
            []
        ));

        $optionsValue->getLocaleCode()->willReturn('en_US');
        $optionsValue->getScopeCode()->willReturn(null);
        $optionsValue->getOptionCodes()->willReturn(['tagA', 'tagB']);

        $this->normalize($optionsValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'my_options_attribute-options' => [
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
        GetAttributes $getAttributes
    ) {
        $optionsValue->getAttributeCode()->willReturn('my_options_attribute');
        $getAttributes->forCode('my_options_attribute')->willReturn(new Attribute(
            'my_options_attribute',
            'pim_catalog_multiselect',
            [],
            false,
            false,
            null,
            true,
            'options',
            []
        ));

        $optionsValue->getLocaleCode()->willReturn(null);
        $optionsValue->getScopeCode()->willReturn('ecommerce');

        $optionsValue->getOptionCodes()->willReturn(['tagA', 'tagB']);

        $this->normalize($optionsValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'my_options_attribute-options' => [
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
        GetAttributes $getAttributes
    ) {
        $optionsValue->getAttributeCode()->willReturn('my_options_attribute');
        $getAttributes->forCode('my_options_attribute')->willReturn(new Attribute(
            'my_options_attribute',
            'pim_catalog_multiselect',
            [],
            false,
            false,
            null,
            true,
            'options',
            []
        ));

        $optionsValue->getLocaleCode()->willReturn('en_US');
        $optionsValue->getScopeCode()->willReturn('ecommerce');

        $optionsValue->getOptionCodes()->willReturn(['tagA', 'tagB']);

        $this->normalize($optionsValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'my_options_attribute-options' => [
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
