<?php

namespace spec\Pim\Bundle\TransformBundle\Normalizer\MongoDB;

use Pim\Bundle\CatalogBundle\Model\Association;
use Pim\Bundle\CatalogBundle\Model\Product;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\MongoDB\MongoObjectsFactory;
use PhpSpec\ObjectBehavior;

/**
 * @require \MongoId
 */
class AssociationNormalizerSpec extends ObjectBehavior
{
    function let(MongoObjectsFactory $mongoFactory)
    {
         $this->beConstructedWith($mongoFactory);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_normalization_of_association_in_mongodb_document(Association $assoc)
    {
        $this->supportsNormalization($assoc, 'mongodb_document')->shouldReturn(true);
    }

    function it_does_not_support_normalization_of_other_entities(\StdClass $object)
    {
        $this->supportsNormalization($object, 'mongodb_document')->shouldReturn(false);
    }

    function it_does_not_support_normalization_of_association_into_other_format(Association $assoc)
    {
        $this->supportsNormalization($assoc, 'json')->shouldReturn(false);
    }

    function it_normalizes_an_association_without_product_or_group(
        $mongoFactory,
        Association $assoc,
        AssociationType $assocType,
        \MongoId $mongoId,
        \MongoDBRef $ownerRef
    ) {
        $assocType->getId()->willReturn(8);
        $assoc->getAssociationType()->willReturn($assocType);
        $assoc->getProducts()->willReturn([]);
        $assoc->getGroups()->willReturn([]);
        $context = ['_id' => '1234abc', 'collection_name' => 'product'];
        $mongoFactory->createMongoId()->willReturn($mongoId);
        $mongoFactory->createMongoDBRef('1234abc', 'product')->willReturn($ownerRef);

        $this->normalize($assoc, 'mongodb_document', $context)->shouldReturn([
            '_id'             => $mongoId,
            'associationType' => 8,
            'owner'           => $ownerRef,
            'products'        => [],
            'groupIds'        => []

        ]);
    }

    function it_normalizes_an_association_with_products(
        $mongoFactory,
        Association $assoc,
        AssociationType $assocType,
        \MongoId $mongoId,
        \MongoDBRef $ownerRef,
        Product $product1,
        \MongoDBRef $product1Ref,
        Product $product2,
        \MongoDBRef $product2Ref
    ) {
        $assocType->getId()->willReturn(8);
        $assoc->getAssociationType()->willReturn($assocType);
        $assoc->getGroups()->willReturn([]);
        $context = ['_id' => '1234abc', 'collection_name' => 'product'];
        $mongoFactory->createMongoId()->willReturn($mongoId);
        $mongoFactory->createMongoDBRef('1234abc', 'product')->willReturn($ownerRef);
        $mongoFactory->createMongoDBRef('product1', 'product')->willReturn($product1Ref);
        $mongoFactory->createMongoDBRef('product2', 'product')->willReturn($product2Ref);

        $product1->getId()->willReturn('product1');
        $product2->getId()->willReturn('product2');

        $assoc->getProducts()->willReturn([$product1, $product2]);

        $this->normalize($assoc, 'mongodb_document', $context)->shouldReturn([
            '_id'             => $mongoId,
            'associationType' => 8,
            'owner'           => $ownerRef,
            'products'        => [$product1Ref, $product2Ref],
            'groupIds'        => []

        ]);
    }

    function it_normalizes_an_association_with_groups(
        $mongoFactory,
        Association $assoc,
        AssociationType $assocType,
        \MongoId $mongoId,
        \MongoDBRef $ownerRef,
        Group $group1,
        Group $group2
    ) {
        $assocType->getId()->willReturn(8);
        $assoc->getProducts()->willReturn([]);
        $assoc->getAssociationType()->willReturn($assocType);
        $context = ['_id' => '1234abc', 'collection_name' => 'product'];
        $mongoFactory->createMongoId()->willReturn($mongoId);
        $mongoFactory->createMongoDBRef('1234abc', 'product')->willReturn($ownerRef);

        $group1->getId()->willReturn(1);
        $group2->getId()->willReturn(2);

        $assoc->getGroups()->willReturn([$group1, $group2]);

        $this->normalize($assoc, 'mongodb_document', $context)->shouldReturn([
            '_id'             => $mongoId,
            'associationType' => 8,
            'owner'           => $ownerRef,
            'products'        => [],
            'groupIds'        => [1, 2]

        ]);
    }

    function it_normalizes_an_association_with_products_and_groups(
        $mongoFactory,
        Association $assoc,
        AssociationType $assocType,
        \MongoId $mongoId,
        \MongoDBRef $ownerRef,
        Product $product1,
        \MongoDBRef $product1Ref,
        Product $product2,
        \MongoDBRef $product2Ref,
        Group $group1,
        Group $group2
    ) {
        $assocType->getId()->willReturn(8);
        $assoc->getAssociationType()->willReturn($assocType);
        $context = ['_id' => '1234abc', 'collection_name' => 'product'];
        $mongoFactory->createMongoId()->willReturn($mongoId);
        $mongoFactory->createMongoDBRef('1234abc', 'product')->willReturn($ownerRef);
        $mongoFactory->createMongoDBRef('product1', 'product')->willReturn($product1Ref);
        $mongoFactory->createMongoDBRef('product2', 'product')->willReturn($product2Ref);

        $product1->getId()->willReturn('product1');
        $product2->getId()->willReturn('product2');

        $assoc->getProducts()->willReturn([$product1, $product2]);

        $group1->getId()->willReturn(1);
        $group2->getId()->willReturn(2);

        $assoc->getGroups()->willReturn([$group1, $group2]);

        $this->normalize($assoc, 'mongodb_document', $context)->shouldReturn([
            '_id'             => $mongoId,
            'associationType' => 8,
            'owner'           => $ownerRef,
            'products'        => [$product1Ref, $product2Ref],
            'groupIds'        => [1, 2]

        ]);
    }
}
