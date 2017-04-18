<?php

namespace spec\Pim\Component\ReferenceData\Normalizer\Indexing\Product;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\ReferenceData\Normalizer\Indexing\Product\ReferenceDataCollectionNormalizer;
use Pim\Component\ReferenceData\ProductValue\ReferenceDataCollectionProductValue;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ReferenceDataCollectionNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceDataCollectionNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_support_reference_data_product_value(
        ReferenceDataCollectionProductValue $referenceDataCollectionProductValue,
        ProductValueInterface $textValue,
        AttributeInterface $referenceData,
        AttributeInterface $textAttribute
    ) {
        $referenceDataCollectionProductValue->getAttribute()->willReturn($referenceData);
        $textValue->getAttribute()->willReturn($textAttribute);

        $this->supportsNormalization(new \stdClass(), 'indexing')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);

        $this->supportsNormalization($textValue, 'indexing')->shouldReturn(false);
        $this->supportsNormalization($referenceDataCollectionProductValue, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($referenceDataCollectionProductValue, 'indexing')->shouldReturn(true);
    }

    function it_normalize_an_empty_reference_data_collection_product_value(
        ReferenceDataCollectionProductValue $referenceDataCollectionProductValue,
        AttributeInterface $referenceData
    ) {
        $referenceDataCollectionProductValue->getAttribute()->willReturn($referenceData);
        $referenceData->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTIONS);

        $referenceDataCollectionProductValue->getLocale()->willReturn(null);
        $referenceDataCollectionProductValue->getScope()->willReturn(null);

        $referenceData->getCode()->willReturn('color');

        $referenceDataCollectionProductValue->getReferenceDataCodes()->willReturn([]);

        $this->normalize($referenceDataCollectionProductValue, 'indexing')->shouldReturn(
            [
                'color-reference_data_options' => [
                    '<all_channels>' => [
                        '<all_locales>' => [],
                    ],
                ],
            ]
        );
    }

    function it_normalize_a_reference_data_collection_product_value_with_no_locale_and_no_channel(
        ReferenceDataCollectionProductValue $referenceDataCollectionProductValue,
        AttributeInterface $referenceData
    ) {
        $referenceDataCollectionProductValue->getAttribute()->willReturn($referenceData);
        $referenceData->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTIONS);

        $referenceDataCollectionProductValue->getLocale()->willReturn(null);
        $referenceDataCollectionProductValue->getScope()->willReturn(null);

        $referenceData->getCode()->willReturn('color');

        $referenceDataCollectionProductValue->getReferenceDataCodes()->willReturn(['fabricA', 'fabricB']);

        $this->normalize($referenceDataCollectionProductValue, 'indexing')->shouldReturn(
            [
                'color-reference_data_options' => [
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
        ReferenceDataCollectionProductValue $referenceDataCollectionProductValue,
        AttributeInterface $referenceData
    ) {
        $referenceDataCollectionProductValue->getAttribute()->willReturn($referenceData);
        $referenceData->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTIONS);

        $referenceDataCollectionProductValue->getLocale()->willReturn('en_US');
        $referenceDataCollectionProductValue->getScope()->willReturn(null);

        $referenceData->getCode()->willReturn('color');

        $referenceDataCollectionProductValue->getReferenceDataCodes()->willReturn(['fabricA', 'fabricB']);

        $this->normalize($referenceDataCollectionProductValue, 'indexing')->shouldReturn(
            [
                'color-reference_data_options' => [
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
        ReferenceDataCollectionProductValue $referenceDataCollectionProductValue,
        AttributeInterface $referenceData
    ) {
        $referenceDataCollectionProductValue->getAttribute()->willReturn($referenceData);
        $referenceData->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTIONS);

        $referenceDataCollectionProductValue->getLocale()->willReturn(null);
        $referenceDataCollectionProductValue->getScope()->willReturn('ecommerce');

        $referenceData->getCode()->willReturn('color');

        $referenceDataCollectionProductValue->getReferenceDataCodes()->willReturn(['fabricA', 'fabricB']);

        $this->normalize($referenceDataCollectionProductValue, 'indexing')->shouldReturn(
            [
                'color-reference_data_options' => [
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
        ReferenceDataCollectionProductValue $referenceDataCollectionProductValue,
        AttributeInterface $referenceData
    ) {
        $referenceDataCollectionProductValue->getAttribute()->willReturn($referenceData);
        $referenceData->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTIONS);

        $referenceDataCollectionProductValue->getLocale()->willReturn('en_US');
        $referenceDataCollectionProductValue->getScope()->willReturn('ecommerce');

        $referenceData->getCode()->willReturn('color');

        $referenceDataCollectionProductValue->getReferenceDataCodes()->willReturn(['fabricA', 'fabricB']);

        $this->normalize($referenceDataCollectionProductValue, 'indexing')->shouldReturn(
            [
                'color-reference_data_options' => [
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
