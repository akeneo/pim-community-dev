<?php

namespace spec\Pim\Bundle\TransformBundle\Transformer;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AssociationInterface;
use Pim\Bundle\CatalogBundle\Model\AssociationTypeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfo;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoTransformerInterface;
use Pim\Bundle\TransformBundle\Transformer\Guesser\GuesserInterface;
use Pim\Bundle\TransformBundle\Transformer\Property\DefaultTransformer;
use Prophecy\Argument;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class AssociationTransformerSpec extends ObjectBehavior
{
    function let(ManagerRegistry $doctrine, PropertyAccessorInterface $propertyAccessor, GuesserInterface $guesser, ColumnInfoTransformerInterface $colInfoTransformer)
    {
        $this->beConstructedWith($doctrine, $propertyAccessor, $guesser, $colInfoTransformer, 'productClass', 'Pim\Bundle\CatalogBundle\Entity\AssociationType');
    }

    function it_transforms_a_product_association(
        $doctrine,
        EntityManager $em,
        IdentifiableObjectRepositoryInterface $repository,
        AssociationTypeInterface $pack,
        ProductInterface $mug,
        $colInfoTransformer,
        ColumnInfo $columnInfo,
        $guesser,
        DefaultTransformer $defaultTransformer,
        ClassMetadata $metadata,
        AssociationInterface $association
    ) {
        $class = 'Pim\Bundle\CatalogBundle\Entity\AssociationType';
        $data  = ['owner' => 'mug-001', 'association_type' => 'PACK'];

        $colInfoTransformer->transform($class, Argument::any())->willReturn($columnInfo);
        $columnInfo->getLabel()->willReturn('code');
        $em->getClassMetadata($class)->willReturn($metadata);
        $guesser->getTransformerInfo($columnInfo, $metadata)->willReturn([$defaultTransformer, []]);
        $columnInfo->getPropertyPath()->willReturn('code');

        $doctrine->getManagerForClass($class)->willReturn($em);
        $em->getRepository($class)->willReturn($repository);
        $repository->findOneByIdentifier('PACK')->willReturn($pack);

        $doctrine->getManagerForClass('productClass')->willReturn($em);
        $em->getRepository('productClass')->willReturn($repository);
        $repository->findOneByIdentifier('mug-001')->willReturn($mug);

        $mug->getAssociationForTypeCode('PACK')->shouldBeCalled()->willReturn($association);
        $association->getOwner()->willReturn($mug);
        $em->persist($mug)->shouldBeCalled();

        $this->transform($class, $data, []);
    }

    function it_throws_an_exception_if_the_owner_is_not_defined()
    {
        $class = 'Pim\Bundle\CatalogBundle\Entity\AssociationType';
        $data  = ['association_type' => 'PACK'];

        $this->shouldThrow('Akeneo\Bundle\BatchBundle\Item\InvalidItemException')->during('transform', [$class, $data, []]);
    }

    function it_throws_an_exception_if_the_owner_does_not_exist(
        $doctrine,
        EntityManager $em,
        IdentifiableObjectRepositoryInterface $repository,
        AssociationTypeInterface $pack
    ) {
        $class = 'Pim\Bundle\CatalogBundle\Entity\AssociationType';
        $data  = ['owner' => 'mug-001'];

        $doctrine->getManagerForClass($class)->willReturn($em);
        $em->getRepository($class)->willReturn($repository);
        $repository->findOneByIdentifier('PACK')->willReturn($pack);

        $doctrine->getManagerForClass('productClass')->willReturn($em);
        $em->getRepository('productClass')->willReturn($repository);
        $repository->findOneByIdentifier('mug-001')->willReturn(null);

        $this->shouldThrow('Akeneo\Bundle\BatchBundle\Item\InvalidItemException')->during('transform', [$class, $data, []]);
    }

    function it_throws_an_exception_if_the_association_type_is_not_defined()
    {
        $class = 'Pim\Bundle\CatalogBundle\Entity\AssociationType';
        $data  = ['owner' => 'mug-001'];

        $this->shouldThrow('Akeneo\Bundle\BatchBundle\Item\InvalidItemException')->during('transform', [$class, $data, []]);
    }

    function it_throws_an_exception_if_the_association_type_does_not_exist(
        $doctrine,
        EntityManager $em,
        IdentifiableObjectRepositoryInterface $repository
    ) {
        $class = 'Pim\Bundle\CatalogBundle\Entity\AssociationType';
        $data  = ['owner' => 'mug-001'];

        $doctrine->getManagerForClass($class)->willReturn($em);
        $em->getRepository($class)->willReturn($repository);
        $repository->findOneByIdentifier('PACK')->willReturn(null);

        $this->shouldThrow('Akeneo\Bundle\BatchBundle\Item\InvalidItemException')->during('transform', [$class, $data, []]);
    }
}
