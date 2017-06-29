<?php

namespace spec\Pim\Component\Catalog\Normalizer\Storage\Product;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Value\ScalarValue;
use Pim\Component\Catalog\Model\ValueCollection;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Model\ValueInterface;
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
        $attribute = new Attribute();
        $attribute->setCode('attribute');
        $attribute->setBackendType('text');
        $realValue = new ScalarValue($attribute, null, null, null);

        $valuesCollection = new ValueCollection([$realValue]);
        $valuesArray = [$realValue];
        $emptyValuesCollection = new ValueCollection();
        $randomCollection = new ArrayCollection([new \stdClass()]);
        $randomArray = [new \stdClass()];

        $this->supportsNormalization($valuesCollection, 'storage')->shouldReturn(true);
        $this->supportsNormalization($valuesArray, 'storage')->shouldReturn(false);
        $this->supportsNormalization($emptyValuesCollection, 'storage')->shouldReturn(true);
        $this->supportsNormalization($randomCollection, 'storage')->shouldReturn(false);
        $this->supportsNormalization($randomArray, 'storage')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'storage')->shouldReturn(false);
        $this->supportsNormalization($valuesCollection, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')->shouldReturn(false);
    }

    function it_normalizes_collection_of_product_values_in_storage_format(
        $serializer,
        ValueInterface $textValue,
        AttributeInterface $textAttribute,
        ValueInterface $descriptionEcommerceFrValue,
        ValueInterface $descriptionEcommerceEnValue,
        ValueInterface $descriptionPrintFrValue,
        AttributeInterface $descriptionAttribute,
        ValueCollectionInterface $values,
        \ArrayIterator $valuesIterator
    ) {
        $values->getIterator()->willReturn($valuesIterator);
        $valuesIterator->rewind()->shouldBeCalled();
        $valuesIterator->valid()->willReturn(true, true, true, true, false);
        $valuesIterator->current()->willReturn(
            $textValue,
            $descriptionEcommerceFrValue,
            $descriptionEcommerceEnValue,
            $descriptionPrintFrValue
        );
        $valuesIterator->next()->shouldBeCalled();

        $textValue->getAttribute()->willReturn($textAttribute);
        $descriptionEcommerceFrValue->getAttribute()->willReturn($descriptionAttribute);
        $descriptionEcommerceEnValue->getAttribute()->willReturn($descriptionAttribute);
        $descriptionPrintFrValue->getAttribute()->willReturn($descriptionAttribute);

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
            ->normalize($values, 'storage')
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
