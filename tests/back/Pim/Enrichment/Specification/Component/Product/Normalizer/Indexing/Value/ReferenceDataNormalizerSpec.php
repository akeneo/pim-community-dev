<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ReferenceDataNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataValue;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ReferenceDataNormalizerSpec extends ObjectBehavior
{
    function let(GetAttributes $getAttributes)
    {
        $this->beConstructedWith($getAttributes);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceDataNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_support_reference_data_product_value(
        ReferenceDataValue $referenceDataProductValue,
        ValueInterface $textValue,
        GetAttributes $getAttributes
    ) {
        $referenceDataProductValue->getAttributeCode()->willReturn('my_referencedata_attribute');
        $textValue->getAttributeCode()->willReturn('my_text_attribute');

        $getAttributes->forCode('my_referencedata_attribute')->willReturn(new Attribute(
            'my_referencedata_attribute',
            'pim_reference_data_simpleselect',
            [],
            false,
            false,
            null,
            true,
            'reference_data_option',
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

        $this->supportsNormalization(new \stdClass(), ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);

        $this->supportsNormalization($textValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($referenceDataProductValue, 'whatever')->shouldReturn(false);

        $this->supportsNormalization($referenceDataProductValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(true);
    }

    function it_normalize_an_empty_reference_data_product_value(
        ReferenceDataValue $referenceDataValue,
        GetAttributes $getAttributes
    ) {
        $referenceDataValue->getAttributeCode()->willReturn('my_referencedata_attribute');
        $getAttributes->forCode('my_referencedata_attribute')->willReturn(new Attribute(
            'my_referencedata_attribute',
            'pim_reference_data_simpleselect',
            [],
            false,
            false,
            null,
            true,
            'reference_data_option',
            []
        ));

        $referenceDataValue->getLocaleCode()->willReturn(null);
        $referenceDataValue->getScopeCode()->willReturn(null);

        $referenceDataValue->getData()->willReturn(null);

        $this->normalize($referenceDataValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'my_referencedata_attribute-reference_data_option' => [
                    '<all_channels>' => [
                        '<all_locales>' => null,
                    ],
                ],
            ]
        );
    }

    function it_normalize_a_reference_data_product_value_with_no_locale_and_no_channel(
        ReferenceDataValue $referenceDataValue,
        GetAttributes $getAttributes
    ) {
        $referenceDataValue->getAttributeCode()->willReturn('my_referencedata_attribute');
        $getAttributes->forCode('my_referencedata_attribute')->willReturn(new Attribute(
            'my_referencedata_attribute',
            'pim_reference_data_simpleselect',
            [],
            false,
            false,
            null,
            true,
            'reference_data_option',
            []
        ));

        $referenceDataValue->getLocaleCode()->willReturn(null);
        $referenceDataValue->getScopeCode()->willReturn(null);

        $referenceDataValue->getData()->willReturn('red');

        $this->normalize($referenceDataValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'my_referencedata_attribute-reference_data_option' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'red',
                    ],
                ],
            ]
        );
    }

    function it_normalizes_an_option_product_value_with_locale(
        ReferenceDataValue $referenceDataValue,
        GetAttributes $getAttributes
    ){
        $referenceDataValue->getAttributeCode()->willReturn('my_referencedata_attribute');
        $getAttributes->forCode('my_referencedata_attribute')->willReturn(new Attribute(
            'my_referencedata_attribute',
            'pim_reference_data_simpleselect',
            [],
            false,
            false,
            null,
            true,
            'reference_data_option',
            []
        ));

        $referenceDataValue->getLocaleCode()->willReturn('en_US');
        $referenceDataValue->getScopeCode()->willReturn(null);

        $referenceDataValue->getData()->willReturn('red');

        $this->normalize($referenceDataValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'my_referencedata_attribute-reference_data_option' => [
                    '<all_channels>' => [
                        'en_US' => 'red',
                    ],
                ],
            ]
        );
    }

    function it_normalizes_a_reference_data_product_value_with_channel(
        ReferenceDataValue $referenceDataValue,
        GetAttributes $getAttributes
    ){
        $referenceDataValue->getAttributeCode()->willReturn('my_referencedata_attribute');
        $getAttributes->forCode('my_referencedata_attribute')->willReturn(new Attribute(
            'my_referencedata_attribute',
            'pim_reference_data_simpleselect',
            [],
            false,
            false,
            null,
            true,
            'reference_data_option',
            []
        ));

        $referenceDataValue->getLocaleCode()->willReturn(null);
        $referenceDataValue->getScopeCode()->willReturn('ecommerce');

        $referenceDataValue->getData()->willReturn('red');

        $this->normalize($referenceDataValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'my_referencedata_attribute-reference_data_option' => [
                    'ecommerce' => [
                        '<all_locales>' => 'red',
                    ],
                ],
            ]
        );
    }

    function it_normalizes_a_reference_data_product_value_with_locale_and_channel(
        ReferenceDataValue $referenceDataValue,
        GetAttributes $getAttributes
    ) {
        $referenceDataValue->getAttributeCode()->willReturn('my_referencedata_attribute');
        $getAttributes->forCode('my_referencedata_attribute')->willReturn(new Attribute(
            'my_referencedata_attribute',
            'pim_reference_data_simpleselect',
            [],
            false,
            false,
            null,
            true,
            'reference_data_option',
            []
        ));

        $referenceDataValue->getLocaleCode()->willReturn('en_US');
        $referenceDataValue->getScopeCode()->willReturn('ecommerce');

        $referenceDataValue->getData()->willReturn('red');

        $this->normalize($referenceDataValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'my_referencedata_attribute-reference_data_option' => [
                    'ecommerce' => [
                        'en_US' => 'red',
                    ],
                ],
            ]
        );
    }
}
