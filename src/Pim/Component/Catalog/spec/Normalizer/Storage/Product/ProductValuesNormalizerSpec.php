<?php

namespace spec\Pim\Component\Catalog\Normalizer\Storage\Product;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValue;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Normalizer\Storage\Product\ProductValuesNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class ProductValuesNormalizerSpec extends ObjectBehavior
{
    function let(SerializerInterface $serializer)
    {
        $serializer->implement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->setSerializer($serializer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductValuesNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\SerializerAwareInterface');
    }

    function it_supports_storage_format_and_collection_values()
    {
        $valuesCollection = new ArrayCollection([new ProductValue()]);
        $valuesArray = [new ProductValue()];
        $emptyValuesCollection = new ArrayCollection();
        $randomCollection = new ArrayCollection([new \stdClass()]);
        $randomArray = [new \stdClass()];

        $this->supportsNormalization($valuesCollection, 'storage')->shouldReturn(true);
        $this->supportsNormalization($valuesArray, 'storage')->shouldReturn(true);
        $this->supportsNormalization($emptyValuesCollection, 'storage')->shouldReturn(true);
        $this->supportsNormalization($randomCollection, 'storage')->shouldReturn(false);
        $this->supportsNormalization($randomArray, 'storage')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'storage')->shouldReturn(false);
        $this->supportsNormalization($valuesCollection, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')->shouldReturn(false);
    }

    function it_normalizes_collection_of_product_values_in_storage_format(
        $serializer,
        ProductValueInterface $textValue,
        AttributeInterface $textAttribute,
        ProductValueInterface $descriptionEcommerceFrValue,
        ProductValueInterface $descriptionEcommerceEnValue,
        ProductValueInterface $descriptionPrintFrValue,
        AttributeInterface $descriptionAttribute
    ) {
        $textValue->getAttribute()->willReturn($textAttribute);
        $descriptionEcommerceFrValue->getAttribute()->willReturn($descriptionAttribute);

        $textAttribute->getCode()->willReturn('text');
        $descriptionAttribute->getCode()->willReturn('description');

        $rawTextValue = [];
        $rawTextValue['text']['<all_channels>']['<all_locales>'] = 'foo';

        $serializer
            ->normalize($textValue, 'storage', [])
            ->shouldBeCalled()
            ->willReturn($rawTextValue);

        $rawDescriptionEcommerceFr = [];
        $rawDescriptionEcommerceFr['description']['ecommerce']['fr'] = 'desc eco fr';

        $serializer
            ->normalize($descriptionEcommerceFrValue, 'storage', [])
            ->shouldBeCalled()
            ->willReturn($rawDescriptionEcommerceFr);

        $rawDescriptionEcommerceEn = [];
        $rawDescriptionEcommerceEn['description']['ecommerce']['en'] = 'desc eco en';

        $serializer
            ->normalize($descriptionEcommerceEnValue, 'storage', [])
            ->shouldBeCalled()
            ->willReturn($rawDescriptionEcommerceEn);

        $rawDescriptionPrintFr = [];
        $rawDescriptionPrintFr['description']['print']['fr'] = 'desc print fr';

        $serializer
            ->normalize($descriptionPrintFrValue, 'storage', [])
            ->shouldBeCalled()
            ->willReturn($rawDescriptionPrintFr);

        $this
            ->normalize(
                [$textValue, $descriptionEcommerceFrValue, $descriptionEcommerceEnValue, $descriptionPrintFrValue],
                'storage'
            )
            ->shouldReturn(
                [
                    'text'   => [
                        '<all_channels>' => [
                            '<all_locales>' => 'foo',
                        ],
                    ],
                    'description'  => [
                        'ecommerce' => [
                            'fr' => 'desc eco fr',
                            'en' => 'desc eco en',
                        ],
                        'print' => [
                            'fr' => 'desc print fr',
                        ],
                    ],
                ]
            );
    }
}
