<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ReferenceDataCollectionNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataCollectionValue;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ReferenceDataCollectionNormalizerSpec extends ObjectBehavior
{
    function let(GetAttributes $getAttributes)
    {
        $this->beConstructedWith($getAttributes);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceDataCollectionNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_support_reference_data_product_value(
        ReferenceDataCollectionValue $referenceDataCollectionProductValue,
        ValueInterface $textValue,
        GetAttributes $getAttributes
    ) {
        $referenceDataCollectionProductValue->getAttributeCode()->willReturn('my_referencedata_collection_attribute');
        $textValue->getAttributeCode()->willReturn('my_text_attribute');

        $getAttributes->forCode('my_referencedata_collection_attribute')->willReturn(new Attribute(
            'my_referencedata_collection_attribute',
            'pim_reference_data_multiselect',
            [],
            false,
            false,
            null,
            true,
            'reference_data_options',
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
        $this->supportsNormalization($referenceDataCollectionProductValue, 'whatever')->shouldReturn(false);

        $this->supportsNormalization(
            $referenceDataCollectionProductValue,
            ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
        )->shouldReturn(true);
    }

    function it_normalize_an_empty_reference_data_collection_product_value(
        ReferenceDataCollectionValue $referenceDataCollectionProductValue,
        GetAttributes $getAttributes
    ) {
        $referenceDataCollectionProductValue->getAttributeCode()->willReturn('my_referencedata_collection_attribute');
        $getAttributes->forCode('my_referencedata_collection_attribute')->willReturn(new Attribute(
            'my_referencedata_collection_attribute',
            'pim_reference_data_multiselect',
            [],
            false,
            false,
            null,
            true,
            'reference_data_options',
            []
        ));

        $referenceDataCollectionProductValue->getLocaleCode()->willReturn(null);
        $referenceDataCollectionProductValue->getScopeCode()->willReturn(null);

        $referenceDataCollectionProductValue->getReferenceDataCodes()->willReturn([]);

        $this->normalize($referenceDataCollectionProductValue,
            ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'my_referencedata_collection_attribute-reference_data_options' => [
                    '<all_channels>' => [
                        '<all_locales>' => [],
                    ],
                ],
            ]
        );
    }

    function it_normalize_a_reference_data_collection_product_value_with_no_locale_and_no_channel(
        ReferenceDataCollectionValue $referenceDataCollectionProductValue,
        GetAttributes $getAttributes
    ) {
        $referenceDataCollectionProductValue->getAttributeCode()->willReturn('my_referencedata_collection_attribute');
        $getAttributes->forCode('my_referencedata_collection_attribute')->willReturn(new Attribute(
            'my_referencedata_collection_attribute',
            'pim_reference_data_multiselect',
            [],
            false,
            false,
            null,
            true,
            'reference_data_options',
            []
        ));

        $referenceDataCollectionProductValue->getLocaleCode()->willReturn(null);
        $referenceDataCollectionProductValue->getScopeCode()->willReturn(null);

        $referenceDataCollectionProductValue->getReferenceDataCodes()->willReturn(['fabricA', 'fabricB']);

        $this->normalize($referenceDataCollectionProductValue,
            ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'my_referencedata_collection_attribute-reference_data_options' => [
                    '<all_channels>' => [
                        '<all_locales>' => [
                            'fabricA',
                            'fabricB',
                        ],
                    ],
                ],
            ]
        );
    }

    function it_normalize_a_reference_data_collection_product_value_with_locale(
        ReferenceDataCollectionValue $referenceDataCollectionProductValue,
        GetAttributes $getAttributes
    ) {
        $referenceDataCollectionProductValue->getAttributeCode()->willReturn('my_referencedata_collection_attribute');
        $getAttributes->forCode('my_referencedata_collection_attribute')->willReturn(new Attribute(
            'my_referencedata_collection_attribute',
            'pim_reference_data_multiselect',
            [],
            false,
            false,
            null,
            true,
            'reference_data_options',
            []
        ));

        $referenceDataCollectionProductValue->getLocaleCode()->willReturn('en_US');
        $referenceDataCollectionProductValue->getScopeCode()->willReturn(null);

        $referenceDataCollectionProductValue->getReferenceDataCodes()->willReturn(['fabricA', 'fabricB']);

        $this->normalize($referenceDataCollectionProductValue,
            ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'my_referencedata_collection_attribute-reference_data_options' => [
                    '<all_channels>' => [
                        'en_US' => [
                            'fabricA',
                            'fabricB',
                        ],
                    ],
                ],
            ]
        );
    }

    function it_normalize_a_reference_data_collection_product_value_with_channel(
        ReferenceDataCollectionValue $referenceDataCollectionProductValue,
        GetAttributes $getAttributes
    ) {
        $referenceDataCollectionProductValue->getAttributeCode()->willReturn('my_referencedata_collection_attribute');
        $getAttributes->forCode('my_referencedata_collection_attribute')->willReturn(new Attribute(
            'my_referencedata_collection_attribute',
            'pim_reference_data_multiselect',
            [],
            false,
            false,
            null,
            true,
            'reference_data_options',
            []
        ));

        $referenceDataCollectionProductValue->getLocaleCode()->willReturn(null);
        $referenceDataCollectionProductValue->getScopeCode()->willReturn('ecommerce');

        $referenceDataCollectionProductValue->getReferenceDataCodes()->willReturn(['fabricA', 'fabricB']);

        $this->normalize($referenceDataCollectionProductValue,
            ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'my_referencedata_collection_attribute-reference_data_options' => [
                    'ecommerce' => [
                        '<all_locales>' => [
                            'fabricA',
                            'fabricB',
                        ],
                    ],
                ],
            ]
        );
    }

    function it_normalize_a_reference_data_collection_product_value_with_locale_and_channel(
        ReferenceDataCollectionValue $referenceDataCollectionProductValue,
        GetAttributes $getAttributes
    ) {
        $referenceDataCollectionProductValue->getAttributeCode()->willReturn('my_referencedata_collection_attribute');
        $getAttributes->forCode('my_referencedata_collection_attribute')->willReturn(new Attribute(
            'my_referencedata_collection_attribute',
            'pim_reference_data_multiselect',
            [],
            false,
            false,
            null,
            true,
            'reference_data_options',
            []
        ));

        $referenceDataCollectionProductValue->getLocaleCode()->willReturn('en_US');
        $referenceDataCollectionProductValue->getScopeCode()->willReturn('ecommerce');

        $referenceDataCollectionProductValue->getReferenceDataCodes()->willReturn(['fabricA', 'fabricB']);

        $this->normalize($referenceDataCollectionProductValue,
            ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'my_referencedata_collection_attribute-reference_data_options' => [
                    'ecommerce' => [
                        'en_US' => [
                            'fabricA',
                            'fabricB',
                        ],
                    ],
                ],
            ]
        );
    }
}
