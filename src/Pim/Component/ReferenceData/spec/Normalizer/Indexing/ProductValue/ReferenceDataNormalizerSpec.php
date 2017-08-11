<?php

namespace spec\Pim\Component\ReferenceData\Normalizer\Indexing\ProductValue;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Normalizer\Indexing\Product\ProductNormalizer;
use Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Pim\Component\ReferenceData\Model\AbstractReferenceData;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;
use Pim\Component\ReferenceData\Value\ReferenceDataValue;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ReferenceDataNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(\Pim\Component\ReferenceData\Normalizer\Indexing\ProductValue\ReferenceDataNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_support_reference_data_product_value(
        ReferenceDataValue $referenceDataProductValue,
        ValueInterface $textValue,
        AttributeInterface $referenceData,
        AttributeInterface $textAttribute
    ) {
        $referenceDataProductValue->getAttribute()->willReturn($referenceData);
        $textValue->getAttribute()->willReturn($textAttribute);

        $this->supportsNormalization(new \stdClass(), ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);

        $this->supportsNormalization($textValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn(false);
        $this->supportsNormalization($textValue, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($referenceDataProductValue, 'whatever')->shouldReturn(false);

        $this->supportsNormalization($referenceDataProductValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn(true);
        $this->supportsNormalization($referenceDataProductValue, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(true);
    }

    function it_normalize_an_empty_reference_data_product_value(
        ReferenceDataValue $referenceDataValue,
        AttributeInterface $referenceData
    ) {
        $referenceDataValue->getAttribute()->willReturn($referenceData);
        $referenceData->getBackendType()->willReturn('reference_data_option');

        $referenceDataValue->getLocale()->willReturn(null);
        $referenceDataValue->getScope()->willReturn(null);

        $referenceData->getCode()->willReturn('color');

        $referenceDataValue->getData()->willReturn(null);

        $this->normalize($referenceDataValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn(
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
        Color $color
    ) {
        $referenceDataValue->getAttribute()->willReturn($referenceData);
        $referenceData->getBackendType()->willReturn('reference_data_option');

        $referenceDataValue->getLocale()->willReturn(null);
        $referenceDataValue->getScope()->willReturn(null);

        $referenceData->getCode()->willReturn('color');

        $referenceDataValue->getData()->willReturn($color);
        $color->getCode()->willReturn('red');

        $this->normalize($referenceDataValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn(
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
        Color $color
    ){
        $referenceDataValue->getAttribute()->willReturn($referenceData);
        $referenceData->getBackendType()->willReturn('reference_data_option');

        $referenceDataValue->getLocale()->willReturn('en_US');
        $referenceDataValue->getScope()->willReturn(null);

        $referenceData->getCode()->willReturn('color');

        $referenceDataValue->getData()->willReturn($color);
        $color->getCode()->willReturn('red');

        $this->normalize($referenceDataValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn(
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
        Color $color
    ){
        $referenceDataValue->getAttribute()->willReturn($referenceData);
        $referenceData->getBackendType()->willReturn('reference_data_option');

        $referenceDataValue->getLocale()->willReturn(null);
        $referenceDataValue->getScope()->willReturn('ecommerce');

        $referenceData->getCode()->willReturn('color');

        $referenceDataValue->getData()->willReturn($color);
        $color->getCode()->willReturn('red');

        $this->normalize($referenceDataValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn(
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
        Color $color
    ) {
        $referenceDataValue->getAttribute()->willReturn($referenceData);
        $referenceData->getBackendType()->willReturn('reference_data_option');

        $referenceDataValue->getLocale()->willReturn('en_US');
        $referenceDataValue->getScope()->willReturn('ecommerce');

        $referenceData->getCode()->willReturn('color');

        $referenceDataValue->getData()->willReturn($color);
        $color->getCode()->willReturn('red');

        $this->normalize($referenceDataValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn(
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

class Color extends AbstractReferenceData implements ReferenceDataInterface
{
    public static function getLabelProperty()
    {
        return 'name';
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    public function __toString()
    {
        return 'color';
    }
}
