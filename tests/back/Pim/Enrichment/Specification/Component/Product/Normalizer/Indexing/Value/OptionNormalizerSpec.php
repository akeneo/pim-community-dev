<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\OptionNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class OptionNormalizerSpec extends ObjectBehavior
{
    function let(GetAttributes $getAttributes)
    {
        $this->beConstructedWith($getAttributes);
    }

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
        GetAttributes $getAttributes
    ) {
        $optionValue->getAttributeCode()->willReturn('my_option_attribute');
        $textValue->getAttributeCode()->willReturn('my_text_attribute');

        $getAttributes->forCode('my_option_attribute')->willReturn(new Attribute(
            'my_option_attribute',
            'pim_catalog_simpleselect',
            [],
            false,
            false,
            null,
            true,
            'option',
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

        $this->supportsNormalization($optionValue, 'whatever')->shouldReturn(false);

        $this->supportsNormalization(new \stdClass(), ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($textValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($optionValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(true);
    }

    function it_normalize_an_empty_option_product_value(
        ValueInterface $optionValue,
        GetAttributes $getAttributes
    ) {
        $optionValue->getAttributeCode()->willReturn('my_option_attribute');
        $getAttributes->forCode('my_option_attribute')->willReturn(new Attribute(
            'my_option_attribute',
            'pim_catalog_simpleselect',
            [],
            false,
            false,
            null,
            true,
            'option',
            []
        ));

        $optionValue->getLocaleCode()->willReturn(null);
        $optionValue->getScopeCode()->willReturn(null);
        $optionValue->getData()->willReturn(null);

        $this->normalize($optionValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'my_option_attribute-option' => [
                    '<all_channels>' => [
                        '<all_locales>' => null,
                    ],
                ],
            ]
        );
    }

    function it_normalize_an_option_product_value_with_no_locale_and_no_channel(
        ValueInterface $optionValue,
        GetAttributes $getAttributes
    ) {
        $optionValue->getAttributeCode()->willReturn('my_option_attribute');
        $optionValue->getLocaleCode()->willReturn(null);
        $optionValue->getScopeCode()->willReturn(null);

        $getAttributes->forCode('my_option_attribute')->willReturn(new Attribute(
            'my_option_attribute',
            'pim_catalog_simpleselect',
            [],
            false,
            false,
            null,
            true,
            'option',
            []
        ));

        $optionValue->getData()->willReturn('red');

        $this->normalize($optionValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'my_option_attribute-option' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'red',
                    ],
                ],
            ]
        );
    }

    function it_normalizes_an_option_product_value_with_locale(
        ValueInterface $optionValue,
        GetAttributes $getAttributes
    ) {
        $optionValue->getAttributeCode()->willReturn('my_option_attribute');
        $getAttributes->forCode('my_option_attribute')->willReturn(new Attribute(
            'my_option_attribute',
            'pim_catalog_simpleselect',
            [],
            true,
            false,
            null,
            true,
            'option',
            []
        ));

        $optionValue->getLocaleCode()->willReturn('en_US');
        $optionValue->getScopeCode()->willReturn(null);

        $optionValue->getData()->willReturn('red');

        $this->normalize($optionValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'my_option_attribute-option' => [
                    '<all_channels>' => [
                        'en_US' => 'red',
                    ],
                ],
            ]
        );
    }

    function it_normalizes_an_option_product_value_with_channel(
        ValueInterface $optionValue,
        GetAttributes $getAttributes
    ) {
        $optionValue->getAttributeCode()->willReturn('my_option_attribute');
        $optionValue->getLocaleCode()->willReturn(null);
        $optionValue->getScopeCode()->willReturn('ecommerce');

        $getAttributes->forCode('my_option_attribute')->willReturn(new Attribute(
            'my_option_attribute',
            'pim_catalog_simpleselect',
            [],
            false,
            true,
            null,
            true,
            'option',
            []
        ));

        $optionValue->getData()->willReturn('red');

        $this->normalize($optionValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'my_option_attribute-option' => [
                    'ecommerce' => [
                        '<all_locales>' => 'red',
                    ],
                ],
            ]
        );
    }

    function it_normalizes_an_option_product_value_with_locale_and_channel(
        ValueInterface $optionValue,
        GetAttributes $getAttributes
    ) {
        $optionValue->getAttributeCode()->willReturn('my_option_attribute');
        $getAttributes->forCode('my_option_attribute')->willReturn(new Attribute(
            'my_option_attribute',
            'pim_catalog_simpleselect',
            [],
            true,
            true,
            null,
            true,
            'option',
            []
        ));

        $optionValue->getLocaleCode()->willReturn('en_US');
        $optionValue->getScopeCode()->willReturn('ecommerce');

        $optionValue->getData()->willReturn('red');

        $this->normalize($optionValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'my_option_attribute-option' => [
                    'ecommerce' => [
                        'en_US' => 'red',
                    ],
                ],
            ]
        );
    }
}
