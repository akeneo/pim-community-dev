<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ReferenceDataCollectionNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataCollectionValue;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ReferenceDataCollectionNormalizerSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
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
        AttributeInterface $referenceData,
        AttributeInterface $textAttribute,
        $attributeRepository
    ) {
        $referenceDataCollectionProductValue->getAttributeCode()->willReturn('my_referencedata_attribute');
        $textValue->getAttributeCode()->willReturn('my_text_attribute');

        $attributeRepository->findOneByIdentifier('my_referencedata_attribute')->willReturn($referenceData);
        $attributeRepository->findOneByIdentifier('my_text_attribute')->willReturn($textAttribute);

        $this->supportsNormalization(new \stdClass(), ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);

        $this->supportsNormalization($textValue, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($referenceDataCollectionProductValue, 'whatever')->shouldReturn(false);

        $this->supportsNormalization(
            $referenceDataCollectionProductValue,
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
        )->shouldReturn(true);
    }

    function it_normalize_an_empty_reference_data_collection_product_value(
        ReferenceDataCollectionValue $referenceDataCollectionProductValue,
        AttributeInterface $referenceData,
        $attributeRepository
    ) {
        $referenceDataCollectionProductValue->getAttributeCode()->willReturn('color');
        $referenceData->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTIONS);
        $attributeRepository->findOneByIdentifier('color')->willReturn($referenceData);

        $referenceDataCollectionProductValue->getLocaleCode()->willReturn(null);
        $referenceDataCollectionProductValue->getScopeCode()->willReturn(null);

        $referenceData->getCode()->willReturn('color');

        $referenceDataCollectionProductValue->getReferenceDataCodes()->willReturn([]);

        $this->normalize($referenceDataCollectionProductValue,
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
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
        ReferenceDataCollectionValue $referenceDataCollectionProductValue,
        AttributeInterface $referenceData,
        $attributeRepository
    ) {
        $referenceDataCollectionProductValue->getAttributeCode()->willReturn('color');
        $referenceData->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTIONS);
        $attributeRepository->findOneByIdentifier('color')->willReturn($referenceData);

        $referenceDataCollectionProductValue->getLocaleCode()->willReturn(null);
        $referenceDataCollectionProductValue->getScopeCode()->willReturn(null);

        $referenceData->getCode()->willReturn('color');

        $referenceDataCollectionProductValue->getReferenceDataCodes()->willReturn(['fabricA', 'fabricB']);

        $this->normalize($referenceDataCollectionProductValue,
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
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
        ReferenceDataCollectionValue $referenceDataCollectionProductValue,
        AttributeInterface $referenceData,
        $attributeRepository
    ) {
        $referenceDataCollectionProductValue->getAttributeCode()->willReturn('color');
        $referenceData->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTIONS);
        $attributeRepository->findOneByIdentifier('color')->willReturn($referenceData);

        $referenceDataCollectionProductValue->getLocaleCode()->willReturn('en_US');
        $referenceDataCollectionProductValue->getScopeCode()->willReturn(null);

        $referenceData->getCode()->willReturn('color');

        $referenceDataCollectionProductValue->getReferenceDataCodes()->willReturn(['fabricA', 'fabricB']);

        $this->normalize($referenceDataCollectionProductValue,
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
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
        ReferenceDataCollectionValue $referenceDataCollectionProductValue,
        AttributeInterface $referenceData,
        $attributeRepository
    ) {
        $referenceDataCollectionProductValue->getAttributeCode()->willReturn('color');
        $referenceData->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTIONS);
        $attributeRepository->findOneByIdentifier('color')->willReturn($referenceData);

        $referenceDataCollectionProductValue->getLocaleCode()->willReturn(null);
        $referenceDataCollectionProductValue->getScopeCode()->willReturn('ecommerce');

        $referenceData->getCode()->willReturn('color');

        $referenceDataCollectionProductValue->getReferenceDataCodes()->willReturn(['fabricA', 'fabricB']);

        $this->normalize($referenceDataCollectionProductValue,
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
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
        ReferenceDataCollectionValue $referenceDataCollectionProductValue,
        AttributeInterface $referenceData,
        $attributeRepository
    ) {
        $referenceDataCollectionProductValue->getAttributeCode()->willReturn('color');
        $referenceData->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTIONS);
        $attributeRepository->findOneByIdentifier('color')->willReturn($referenceData);

        $referenceDataCollectionProductValue->getLocaleCode()->willReturn('en_US');
        $referenceDataCollectionProductValue->getScopeCode()->willReturn('ecommerce');

        $referenceData->getCode()->willReturn('color');

        $referenceDataCollectionProductValue->getReferenceDataCodes()->willReturn(['fabricA', 'fabricB']);

        $this->normalize($referenceDataCollectionProductValue,
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
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
