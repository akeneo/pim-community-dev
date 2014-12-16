<?php

namespace spec\Pim\Bundle\TransformBundle\Transformer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoTransformerInterface;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfo;
use Pim\Bundle\TransformBundle\Transformer\Guesser\GuesserInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Doctrine\ORM\EntityManager;
use Pim\Bundle\CatalogBundle\Repository\ReferableEntityRepositoryInterface;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Pim\Bundle\TransformBundle\Transformer\Property\DefaultTransformer;

class AssociationTransformerSpec extends ObjectBehavior
{
    function let(ManagerRegistry $doctrine, PropertyAccessorInterface $propertyAccessor, GuesserInterface $guesser, ColumnInfoTransformerInterface $colInfoTransformer)
    {
        $this->beConstructedWith($doctrine, $propertyAccessor, $guesser, $colInfoTransformer, 'productClass', 'Pim\Bundle\CatalogBundle\Entity\AssociationType');
    }

    function it_transforms_a_product_association(
        $doctrine,
        EntityManager $em,
        ReferableEntityRepositoryInterface $repository,
        AssociationType $pack,
        ProductInterface $mug,
        $colInfoTransformer,
        ColumnInfo $columnInfo,
        $guesser,
        DefaultTransformer $defaultTransformer,
        ClassMetadata $metadata
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
        $repository->findByReference('PACK')->willReturn($pack);

        $doctrine->getManagerForClass('productClass')->willReturn($em);
        $em->getRepository('productClass')->willReturn($repository);
        $repository->findByReference('mug-001')->willReturn($mug);

        $mug->getAssociationForTypeCode('PACK')->shouldBeCalled();

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
        ReferableEntityRepositoryInterface $repository,
        AssociationType $pack
    ) {
        $class = 'Pim\Bundle\CatalogBundle\Entity\AssociationType';
        $data  = ['owner' => 'mug-001'];

        $doctrine->getManagerForClass($class)->willReturn($em);
        $em->getRepository($class)->willReturn($repository);
        $repository->findByReference('PACK')->willReturn($pack);

        $doctrine->getManagerForClass('productClass')->willReturn($em);
        $em->getRepository('productClass')->willReturn($repository);
        $repository->findByReference('mug-001')->willReturn(null);

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
        ReferableEntityRepositoryInterface $repository
    ) {
        $class = 'Pim\Bundle\CatalogBundle\Entity\AssociationType';
        $data  = ['owner' => 'mug-001'];

        $doctrine->getManagerForClass($class)->willReturn($em);
        $em->getRepository($class)->willReturn($repository);
        $repository->findByReference('PACK')->willReturn(null);

        $this->shouldThrow('Akeneo\Bundle\BatchBundle\Item\InvalidItemException')->during('transform', [$class, $data, []]);
    }
}
