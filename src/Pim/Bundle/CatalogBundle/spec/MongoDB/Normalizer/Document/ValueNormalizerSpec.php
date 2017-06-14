<?php

namespace spec\Pim\Bundle\CatalogBundle\MongoDB\Normalizer\Document;

use Akeneo\Bundle\StorageUtilsBundle\MongoDB\MongoObjectsFactory;
use Doctrine\Common\Persistence\ManagerRegistry;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @require \MongoId
 */
class ValueNormalizerSpec extends ObjectBehavior
{
    function let(MongoObjectsFactory $mongoFactory, ManagerRegistry $managerRegistry, SerializerInterface $serializer)
    {
        $this->beConstructedWith($mongoFactory, $managerRegistry);
        $serializer->implement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->setSerializer($serializer);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_normalization_of_product_value_in_mongodb_document(ValueInterface $value)
    {
        $this->supportsNormalization($value, 'mongodb_document')->shouldReturn(true);
    }

    function it_does_not_support_normalization_of_other_entities(\StdClass $object)
    {
        $this->supportsNormalization($object, 'mongodb_document')->shouldReturn(false);
    }

    function it_does_not_support_normalization_of_product_value_into_other_format(ValueInterface $value)
    {
        $this->supportsNormalization($value, 'json')->shouldReturn(false);
    }

    function it_normalizes_a_product_value_into_mongodb_document(
        $mongoFactory,
        $serializer,
        ValueInterface $value,
        AttributeInterface $attribute,
        \MongoDBRef $mongoDBRef,
        \MongoId $mongoId
    ) {
        $context = ['_id' => $mongoId, 'collection_name' => 'product', 'database_name' => 'my_db'];

        $mongoFactory->createMongoId()->willReturn($mongoId);
        $mongoFactory->createMongoDBRef('product', $mongoId, 'my_db')->willReturn($mongoDBRef);

        $attribute->getId()->willReturn(123);
        $attribute->getBackendType()->willReturn('text');
        $attribute->getReferenceDataName()->willReturn(null);

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
