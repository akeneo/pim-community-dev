<?php

namespace spec\Pim\Bundle\TransformBundle\Normalizer\MongoDB;

use Akeneo\Bundle\StorageUtilsBundle\MongoDB\MongoObjectsFactory;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @require \MongoId
 */
class ProductValueNormalizerSpec extends ObjectBehavior
{
    function let(MongoObjectsFactory $mongoFactory, SerializerInterface $serializer)
    {
        $this->beConstructedWith($mongoFactory);
        $serializer->implement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->setSerializer($serializer);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_normalization_of_product_value_in_mongodb_document(ProductValueInterface $value)
    {
        $this->supportsNormalization($value, 'mongodb_document')->shouldReturn(true);
    }

    function it_does_not_support_normalization_of_other_entities(\StdClass $object)
    {
        $this->supportsNormalization($object, 'mongodb_document')->shouldReturn(false);
    }

    function it_does_not_support_normalization_of_product_value_into_other_format(ProductValueInterface $value)
    {
        $this->supportsNormalization($value, 'json')->shouldReturn(false);
    }

    function it_normalizes_a_product_value_into_mongodb_document(
        $mongoFactory,
        $serializer,
        ProductValueInterface $value,
        AttributeInterface $attribute,
        \MongoDBRef $mongoDBRef,
        \MongoId $mongoId
    ) {
        $context = ['_id' => $mongoId, 'collection_name' => 'product'];

        $mongoFactory->createMongoId()->willReturn($mongoId);
        $mongoFactory->createMongoDBRef('product', $mongoId)->willReturn($mongoDBRef);

        $attribute->getId()->willReturn(123);
        $attribute->getBackendType()->willReturn('text');

        $value->getAttribute()->willReturn($attribute);
        $value->getData()->willReturn('my description');
        $value->getLocale()->willReturn(null);
        $value->getScope()->willReturn(null);

        $this->normalize($value, 'mongodb_document', $context)->shouldReturn([
            '_id'       => $mongoId,
            'attribute' => 123,
            'entity'    => $mongoDBRef,
            'text'      => 'my description'
        ]);
    }
}
