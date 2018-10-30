<?php

namespace spec\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Storage\Product;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollectionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Storage\Product\ProductValuesNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ProductValuesNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $normalizer)
    {
        $this->beConstructedWith($normalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductValuesNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
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

    function it_normalizes_an_empty_collection_of_product_values() {
        $this->normalize(new ValueCollection(), 'storage')->shouldReturn([]);
    }

    function it_normalizes_collection_of_product_values_in_storage_format(
        $normalizer,
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

        $normalizer
            ->normalize($textValue, 'storage', [])
            ->shouldBeCalled()
            ->willReturn($rawTextValue);

        $rawDescriptionEcommerceFr = [];
        $rawDescriptionEcommerceFr['description']['ecommerce']['fr'] = 'desc eco fr';

        $normalizer
            ->normalize($descriptionEcommerceFrValue, 'storage', [])
            ->shouldBeCalled()
            ->willReturn($rawDescriptionEcommerceFr);

        $rawDescriptionEcommerceEn = [];
        $rawDescriptionEcommerceEn['description']['ecommerce']['en'] = 'desc eco en';

        $normalizer
            ->normalize($descriptionEcommerceEnValue, 'storage', [])
            ->shouldBeCalled()
            ->willReturn($rawDescriptionEcommerceEn);

        $rawDescriptionPrintFr = [];
        $rawDescriptionPrintFr['description']['print']['fr'] = 'desc print fr';

        $normalizer
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

    function it_normalizes_collection_of_product_values_with_numeric_attribute_code($normalizer) {
        $textAttribute = new Attribute();
        $textAttribute->setCode('123');
        $textAttribute->setLocalizable(true);
        $textAttribute->setScopable(true);
        $textAttribute->setUnique(false);

        $textValueEn = new ScalarValue($textAttribute, 'ecommerce', 'en_US', 'foo');
        $textValueFr = new ScalarValue($textAttribute, 'ecommerce', 'fr_FR', 'foo');
        $values = new ValueCollection([$textValueEn, $textValueFr]);

        $normalizer
            ->normalize($textValueEn, 'storage', [])
            ->shouldBeCalled()
            ->willReturn(['123' => ['ecommerce' => ['en_US' => 'foo']]]);

        $normalizer
            ->normalize($textValueFr, 'storage', [])
            ->shouldBeCalled()
            ->willReturn(['123' => ['ecommerce' => ['fr_FR' => 'baz']]]);

        $this
            ->normalize($values, 'storage')
            ->shouldReturn(
                [
                    '123'   => [
                        'ecommerce' => [
                            'en_US' => 'foo',
                            'fr_FR' => 'baz',
                        ],
                    ],
                ]
            );
    }
}
