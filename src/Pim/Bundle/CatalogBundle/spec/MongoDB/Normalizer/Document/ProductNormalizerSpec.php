<?php

namespace spec\Pim\Bundle\CatalogBundle\MongoDB\Normalizer\Document;

use Akeneo\Bundle\StorageUtilsBundle\MongoDB\MongoObjectsFactory;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\Association;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @require \MongoId
 */
class ProductNormalizerSpec extends ObjectBehavior
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

    function it_supports_normalization_of_product_in_mongodb_document(ProductInterface $product)
    {
        $this->supportsNormalization($product, 'mongodb_document')->shouldReturn(true);
    }

    function it_does_not_support_normalization_of_other_entities(\StdClass $object)
    {
        $this->supportsNormalization($object, 'mongodb_document')->shouldReturn(false);
    }

    function it_does_not_support_normalization_of_product_into_other_format(ProductInterface $product)
    {
        $this->supportsNormalization($product, 'json')->shouldReturn(false);
    }

    function it_normalizes_a_product_into_mongodb_document(
        $mongoFactory,
        $serializer,
        ProductInterface $product,
        \MongoId $mongoId,
        \DateTime $date,
        Association $assoc1,
        Association $assoc2,
        CategoryInterface $category1,
        CategoryInterface $category2,
        GroupInterface $group1,
        GroupInterface $group2,
        ValueInterface $value1,
        ValueInterface $value2,
        FamilyInterface $family
    ) {
        $mongoFactory->createMongoId('product1')->willReturn($mongoId);

        $family->getId()->willReturn(36);

        $category1->getId()->willReturn(12);
        $category2->getId()->willReturn(34);

        $group1->getId()->willReturn(56);
        $group2->getId()->willReturn(78);

        $product->getId()->willReturn('product1');
        $product->getCreated()->willReturn($date);
        $product->getUpdated()->willReturn($date);
        $product->getFamily()->willReturn($family);
        $product->isEnabled()->willReturn(true);
        $product->getGroups()->willReturn([$group1, $group2]);
        $product->getCategories()->willReturn([$category1, $category2]);
        $product->getAssociations()->willReturn([$assoc1, $assoc2]);
        $product->getValues()->willReturn([$value1, $value2]);

        $context = ['_id' => $mongoId];

        $serializer
            ->normalize($product, 'mongodb_json')
            ->willReturn(['data' => 'data', 'completenesses' => 'completenesses']);
        $serializer->normalize($value1, 'mongodb_document', $context)->willReturn('my_value_1');
        $serializer->normalize($value2, 'mongodb_document', $context)->willReturn('my_value_2');
        $serializer->normalize($assoc1, 'mongodb_document', $context)->willReturn('my_assoc_1');
        $serializer->normalize($assoc2, 'mongodb_document', $context)->willReturn('my_assoc_2');
        $serializer->normalize($date, 'mongodb_document', $context)->willReturn($date);

        $this->normalize($product, 'mongodb_document')->shouldReturn([
            '_id'            => $mongoId,
            'created'        => $date,
            'updated'        => $date,
            'family'         => 36,
            'enabled'        => true,
            'groupIds'       => [56, 78],
            'categoryIds'    => [12, 34],
            'associations'   => ['my_assoc_1', 'my_assoc_2'],
            'values'         => ['my_value_1', 'my_value_2'],
            'normalizedData' => ['data' => 'data'],
            'completenesses' => [],
        ]);
    }

    function it_normalizes_a_product_without_family_into_mongodb_document(
        $serializer,
        $mongoFactory,
        ProductInterface $product,
        \MongoId $mongoId,
        \DateTime $date,
        Association $assoc1,
        Association $assoc2,
        CategoryInterface $category1,
        CategoryInterface $category2,
        GroupInterface $group1,
        GroupInterface $group2,
        ValueInterface $value1,
        ValueInterface $value2
    ) {
        $mongoFactory->createMongoId('product1')->willReturn($mongoId);

        $category1->getId()->willReturn(12);
        $category2->getId()->willReturn(34);

        $group1->getId()->willReturn(56);
        $group2->getId()->willReturn(78);

        $product->getId()->willReturn('product1');
        $product->getCreated()->willReturn($date);
        $product->getUpdated()->willReturn($date);
        $product->getFamily()->willReturn(null);
        $product->isEnabled()->willReturn(true);
        $product->getGroups()->willReturn([$group1, $group2]);
        $product->getCategories()->willReturn([$category1, $category2]);
        $product->getAssociations()->willReturn([$assoc1, $assoc2]);
        $product->getValues()->willReturn([$value1, $value2]);

        $context = ['_id' => $mongoId];

        $serializer
            ->normalize($product, 'mongodb_json')
            ->willReturn(['data' => 'data', 'completenesses' => 'completenesses']);
        $serializer->normalize($value1, 'mongodb_document', $context)->willReturn('my_value_1');
        $serializer->normalize($value2, 'mongodb_document', $context)->willReturn('my_value_2');
        $serializer->normalize($assoc1, 'mongodb_document', $context)->willReturn('my_assoc_1');
        $serializer->normalize($assoc2, 'mongodb_document', $context)->willReturn('my_assoc_2');
        $serializer->normalize($date, 'mongodb_document', $context)->willReturn($date);

        $this->normalize($product, 'mongodb_document')->shouldReturn([
            '_id'            => $mongoId,
            'created'        => $date,
            'updated'        => $date,
            'enabled'        => true,
            'groupIds'       => [56, 78],
            'categoryIds'    => [12, 34],
            'associations'   => ['my_assoc_1', 'my_assoc_2'],
            'values'         => ['my_value_1', 'my_value_2'],
            'normalizedData' => ['data' => 'data'],
            'completenesses' => [],
        ]);
    }
}
