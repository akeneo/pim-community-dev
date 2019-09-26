<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ReferenceDataNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataValue;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ReferenceDataNormalizerSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
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
        AttributeInterface $referenceData,
        AttributeInterface $textAttribute,
        $attributeRepository
    ) {
        $referenceDataProductValue->getAttributeCode()->willReturn('my_referencedata_attribute');
        $textValue->getAttributeCode()->willReturn('my_text_attribute');

        $attributeRepository->findOneByIdentifier('my_referencedata_attribute')->willReturn($referenceData);
        $attributeRepository->findOneByIdentifier('my_text_attribute')->willReturn($textAttribute);

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
        AttributeInterface $referenceData,
        $attributeRepository
    ) {
        $referenceDataValue->getAttributeCode()->willReturn('reference_data_option');
        $referenceData->getBackendType()->willReturn('reference_data_option');
        $attributeRepository->findOneByIdentifier('reference_data_option')->willReturn($referenceData);

        $referenceDataValue->getLocaleCode()->willReturn(null);
        $referenceDataValue->getScopeCode()->willReturn(null);

        $referenceData->getCode()->willReturn('color');

        $referenceDataValue->getData()->willReturn(null);

        $this->normalize($referenceDataValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'color-reference_data_option' => [
                    '<all_channels>' => [
                        '<all_locales>' => null,
                    ],
                ],
            ]
        );
    }

    function it_normalize_a_reference_data_product_value_with_no_locale_and_no_channel(
        ReferenceDataValue $referenceDataValue,
        AttributeInterface $referenceData,
        $attributeRepository
    ) {
        $referenceDataValue->getAttributeCode()->willReturn('reference_data_option');
        $referenceData->getBackendType()->willReturn('reference_data_option');
        $attributeRepository->findOneByIdentifier('reference_data_option')->willReturn($referenceData);

        $referenceDataValue->getLocaleCode()->willReturn(null);
        $referenceDataValue->getScopeCode()->willReturn(null);

        $referenceData->getCode()->willReturn('color');

        $referenceDataValue->getData()->willReturn('red');

        $this->normalize($referenceDataValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'color-reference_data_option' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'red',
                    ],
                ],
            ]
        );
    }

    function it_normalizes_an_option_product_value_with_locale(
        ReferenceDataValue $referenceDataValue,
        AttributeInterface $referenceData,
        $attributeRepository
    ){
        $referenceDataValue->getAttributeCode()->willReturn('reference_data_option');
        $referenceData->getBackendType()->willReturn('reference_data_option');
        $attributeRepository->findOneByIdentifier('reference_data_option')->willReturn($referenceData);

        $referenceDataValue->getLocaleCode()->willReturn('en_US');
        $referenceDataValue->getScopeCode()->willReturn(null);

        $referenceData->getCode()->willReturn('color');

        $referenceDataValue->getData()->willReturn('red');

        $this->normalize($referenceDataValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'color-reference_data_option' => [
                    '<all_channels>' => [
                        'en_US' => 'red',
                    ],
                ],
            ]
        );
    }

    function it_normalizes_a_reference_data_product_value_with_channel(
        ReferenceDataValue $referenceDataValue,
        AttributeInterface $referenceData,
        $attributeRepository
    ){
        $referenceDataValue->getAttributeCode()->willReturn('reference_data_option');
        $referenceData->getBackendType()->willReturn('reference_data_option');
        $attributeRepository->findOneByIdentifier('reference_data_option')->willReturn($referenceData);

        $referenceDataValue->getLocaleCode()->willReturn(null);
        $referenceDataValue->getScopeCode()->willReturn('ecommerce');

        $referenceData->getCode()->willReturn('color');

        $referenceDataValue->getData()->willReturn('red');

        $this->normalize($referenceDataValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'color-reference_data_option' => [
                    'ecommerce' => [
                        '<all_locales>' => 'red',
                    ],
                ],
            ]
        );
    }

    function it_normalizes_a_reference_data_product_value_with_locale_and_channel(
        ReferenceDataValue $referenceDataValue,
        AttributeInterface $referenceData,
        $attributeRepository
    ) {
        $referenceDataValue->getAttributeCode()->willReturn('reference_data_option');
        $referenceData->getBackendType()->willReturn('reference_data_option');
        $attributeRepository->findOneByIdentifier('reference_data_option')->willReturn($referenceData);

        $referenceDataValue->getLocaleCode()->willReturn('en_US');
        $referenceDataValue->getScopeCode()->willReturn('ecommerce');

        $referenceData->getCode()->willReturn('color');

        $referenceDataValue->getData()->willReturn('red');

        $this->normalize($referenceDataValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'color-reference_data_option' => [
                    'ecommerce' => [
                        'en_US' => 'red',
                    ],
                ],
            ]
        );
    }
}
