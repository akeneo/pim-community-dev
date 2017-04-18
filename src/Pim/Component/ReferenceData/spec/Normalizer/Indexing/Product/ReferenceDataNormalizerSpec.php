<?php

namespace spec\Pim\Component\ReferenceData\Normalizer\Indexing\Product;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\ReferenceData\Model\AbstractReferenceData;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;
use Pim\Component\ReferenceData\Normalizer\Indexing\Product\ReferenceDataNormalizer;
use Pim\Component\ReferenceData\ProductValue\ReferenceDataProductValue;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ReferenceDataNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceDataNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_support_reference_data_product_value(
        ReferenceDataProductValue $referenceDataProductValue,
        ProductValueInterface $textValue,
        AttributeInterface $referenceData,
        AttributeInterface $textAttribute
    ) {
        $referenceDataProductValue->getAttribute()->willReturn($referenceData);
        $textValue->getAttribute()->willReturn($textAttribute);

        $this->supportsNormalization(new \stdClass(), 'indexing')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);

        $this->supportsNormalization($textValue, 'indexing')->shouldReturn(false);
        $this->supportsNormalization($referenceDataProductValue, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($referenceDataProductValue, 'indexing')->shouldReturn(true);
    }

    function it_normalize_an_empty_reference_data_product_value(
        ReferenceDataProductValue $referenceDataValue,
        AttributeInterface $referenceData
    ) {
        $referenceDataValue->getAttribute()->willReturn($referenceData);
        $referenceData->getBackendType()->willReturn('reference_data_option');

        $referenceDataValue->getLocale()->willReturn(null);
        $referenceDataValue->getScope()->willReturn(null);

        $referenceData->getCode()->willReturn('color');

        $referenceDataValue->getData()->willReturn(null);

        $this->normalize($referenceDataValue, 'indexing')->shouldReturn(
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
        ReferenceDataProductValue $referenceDataValue,
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

        $this->normalize($referenceDataValue, 'indexing')->shouldReturn(
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
        ReferenceDataProductValue $referenceDataValue,
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

        $this->normalize($referenceDataValue, 'indexing')->shouldReturn(
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
        ReferenceDataProductValue $referenceDataValue,
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

        $this->normalize($referenceDataValue, 'indexing')->shouldReturn(
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
        ReferenceDataProductValue $referenceDataValue,
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

        $this->normalize($referenceDataValue, 'indexing')->shouldReturn(
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
