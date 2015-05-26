<?php

namespace spec\Pim\Bundle\TransformBundle\Transformer;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfo;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoTransformerInterface;
use Pim\Bundle\TransformBundle\Transformer\Guesser\GuesserInterface;
use Pim\Bundle\TransformBundle\Transformer\Property\DefaultTransformer;
use Prophecy\Argument;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class AttributeOptionTransformerSpec extends ObjectBehavior
{
    function let(
        ManagerRegistry $doctrine,
        PropertyAccessorInterface $propertyAccessor,
        GuesserInterface $guesser,
        ColumnInfoTransformerInterface $columnInfoTransformer
    ) {
        $this->beConstructedWith($doctrine, $propertyAccessor, $guesser, $columnInfoTransformer);
    }

    function it_is_a_entity_transformer()
    {
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Transformer\EntityTransformer');
    }

    function it_transforms_array_to_attribute_option(
        ManagerRegistry $doctrine,
        EntityManager $em,
        IdentifiableObjectRepositoryInterface $repository,
        AttributeOptionInterface $option,
        ColumnInfoTransformerInterface $columnInfoTransformer,
        ColumnInfo $columnInfo,
        ClassMetadata $metadata,
        GuesserInterface $guesser,
        DefaultTransformer $defaultTransformer,
        AttributeInterface $attribute
    ) {
        $class = 'Pim\Bundle\CatalogBundle\Entity\AttributeOption';
        $data = ['code' => 'blue', 'attribute' => 'color'];

        $doctrine->getManagerForClass($class)->willReturn($em);
        $em->getRepository($class)->willReturn($repository);
        $repository->getIdentifierProperties()->willReturn(['attribute', 'code']);
        $repository->findOneByIdentifier('color.blue')->willReturn($option);

        $columnInfoTransformer->transform($class, Argument::any())->willReturn($columnInfo);
        $columnInfo->getLabel()->willReturn('code');
        $em->getClassMetadata($class)->willReturn($metadata);
        $guesser->getTransformerInfo($columnInfo, $metadata)->willReturn([$defaultTransformer, []]);
        $columnInfo->getPropertyPath()->willReturn('attribute');

        $option->getAttribute()->willReturn($attribute);

        $this->transform($class, $data, []);
    }

    function it_throws_exception_when_attribute_is_unknown(
        ManagerRegistry $doctrine,
        EntityManager $em,
        IdentifiableObjectRepositoryInterface $repository,
        AttributeOptionInterface $option,
        ColumnInfoTransformerInterface $columnInfoTransformer,
        ColumnInfo $columnInfo,
        ClassMetadata $metadata,
        GuesserInterface $guesser,
        DefaultTransformer $defaultTransformer
    ) {
        $class = 'Pim\Bundle\CatalogBundle\Entity\AttributeOption';
        $data = ['code' => 'blue', 'attribute' => 'color'];

        $doctrine->getManagerForClass($class)->willReturn($em);
        $em->getRepository($class)->willReturn($repository);
        $repository->getIdentifierProperties()->willReturn(['attribute', 'code']);
        $repository->findOneByIdentifier('color.blue')->willReturn($option);

        $columnInfoTransformer->transform($class, Argument::any())->willReturn($columnInfo);
        $columnInfo->getLabel()->willReturn('code');
        $em->getClassMetadata($class)->willReturn($metadata);
        $guesser->getTransformerInfo($columnInfo, $metadata)->willReturn([$defaultTransformer, []]);
        $columnInfo->getPropertyPath()->willReturn('attribute');

        $option->getAttribute()->willReturn(null);
        $option->getCode()->willReturn('blue');

        $exception = new \Exception(
            sprintf('The attribute used for option "%s" is not known', 'blue')
        );

        $this->shouldThrow($exception)->duringTransform($class, $data, []);
    }
}
