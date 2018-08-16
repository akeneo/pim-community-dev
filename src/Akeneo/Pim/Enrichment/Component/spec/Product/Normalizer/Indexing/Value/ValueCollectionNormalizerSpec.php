<?php

namespace spec\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Product\ProductNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ValueCollectionNormalizerSpec extends ObjectBehavior
{
    function let(
        SerializerInterface $serializer,
        ValueCollection $valueCollection
    ) {
        $serializer->implement(NormalizerInterface::class);
        $this->setSerializer($serializer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ValueCollectionNormalizer::class);
    }

    function it_support_product_value_collection($valueCollection)
    {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($valueCollection, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($valueCollection, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->shouldReturn(true);

        $this->supportsNormalization(new \stdClass(), ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($valueCollection, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(true);
    }

    function it_normalizes_product_value_collection(
        $valueCollection,
        ValueInterface $value1,
        ValueInterface $value2,
        AttributeInterface $attribute1,
        AttributeInterface $attribute2,
        \ArrayIterator $valueCollectionIterator,
        SerializerInterface $serializer
    ) {
        $valueCollection->getIterator()->willReturn($valueCollectionIterator);
        $valueCollectionIterator->rewind()->shouldBeCalled();
        $valueCollectionIterator->valid()->willReturn(true, true, false);
        $valueCollectionIterator->current()->willReturn($value1, $value2);

        $valueCollectionIterator->next()->shouldBeCalled();

        $value1->getAttribute()->willReturn($attribute1);
        $value2->getAttribute()->willReturn($attribute2);

        $attribute1->getType()->willReturn('pim_catalog_number');
        $attribute2->getType()->willReturn('pim_catalog_text');

        $serializer->normalize($value1, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX, [])->willReturn(
            [
                'box_quantity-decimal' => [
                    '<all_channels>' => [
                        '<all_locales>' => '7',
                    ],
                ],
            ]
        );

        $serializer->normalize($value2, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX, [])->willReturn(
            [
                'description-textarea' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'Nice description for phpspec',
                    ],
                ],
            ]
        );

        $this->normalize($valueCollection, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX,[])->shouldReturn(
            [
                'box_quantity-decimal' => [
                    '<all_channels>' => [
                        '<all_locales>' => '7',
                    ],
                ],
                'description-textarea'     => [
                    '<all_channels>' => [
                        '<all_locales>' => 'Nice description for phpspec',
                    ],
                ],
            ]
        );
    }
}
