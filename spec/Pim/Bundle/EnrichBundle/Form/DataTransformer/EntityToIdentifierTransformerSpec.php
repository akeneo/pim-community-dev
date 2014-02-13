<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\DataTransformer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

class EntityToIdentifierTransformerSpec extends ObjectBehavior
{
    function it_is_a_form_data_transformer(
        ObjectRepository $repository,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->beConstructedWith($repository, false, $propertyAccessor);
        $this->shouldBeAnInstanceOf('Symfony\Component\Form\DataTransformerInterface');
    }

    function it_transforms_value_into_its_id(
        \StdClass $entity,
        ObjectRepository $repository,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->beConstructedWith($repository, false, $propertyAccessor);
        $propertyAccessor->getValue($entity, 'id')->willReturn(30);

        $this->transform($entity)->shouldReturn(30);
    }

    function it_reverse_transforms_id_into_an_entity(
        \StdClass $entity,
        ObjectRepository $repository,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->beConstructedWith($repository, false, $propertyAccessor);
        $repository->find(30)->willReturn($entity);

        $this->reverseTransform(30)->shouldReturn($entity);
    }

    function it_transforms_values_into_ids(
        \StdClass $foo,
        \StdClass $bar,
        ObjectRepository $repository,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->beConstructedWith($repository, true, $propertyAccessor);
        $propertyAccessor->getValue($foo, 'id')->willReturn(4);
        $propertyAccessor->getValue($bar, 'id')->willReturn(8);

        $this->transform([$foo, $bar])->shouldReturn([4, 8]);
    }

    function it_reverse_transforms_ids_into_entities(
        \StdClass $foo,
        \StdClass $bar,
        ObjectRepository $repository,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->beConstructedWith($repository, true, $propertyAccessor);
        $repository->findBy(['id' => [4, 8]])->willReturn([$foo, $bar]);

        $this->reverseTransform([4, 8])->shouldReturn([$foo, $bar]);
    }

    function it_does_not_transform_null_value(
        ObjectRepository $repository,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->beConstructedWith($repository, false, $propertyAccessor);

        $this->transform(null)->shouldReturn(null);
    }

    function it_throws_exception_when_transforming_array_value_in_single_mode(
        ObjectRepository $repository,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->beConstructedWith($repository, false, $propertyAccessor);
        $this->shouldThrow(new UnexpectedTypeException(['foo', 'bar'], 'object'))->duringTransform(['foo', 'bar']);
    }

    function it_throws_exception_when_transforming_scalar_value_in_single_mode(
        ObjectRepository $repository,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->beConstructedWith($repository, false, $propertyAccessor);
        $this->shouldThrow(new UnexpectedTypeException(15, 'object'))->duringTransform(15);
    }

    function it_throws_exception_when_transforming_scalar_value_in_multiple_mode(
        ObjectRepository $repository,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->beConstructedWith($repository, true, $propertyAccessor);
        $this->shouldThrow(new UnexpectedTypeException('foo', 'array'))->duringTransform('foo');
    }

    function it_throws_exception_when_transforming_object_value_in_multiple_mode(
        \StdClass $foo,
        ObjectRepository $repository,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->beConstructedWith($repository, true, $propertyAccessor);
        $this->shouldThrow(new UnexpectedTypeException($foo->getWrappedObject(), 'array'))->duringTransform($foo);
    }

    function it_throws_exception_when_reverse_transforming_non_array_value_in_multiple_mode(
        \StdClass $foo,
        ObjectRepository $repository,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->beConstructedWith($repository, true, $propertyAccessor);
        $this->shouldThrow(new UnexpectedTypeException($foo->getWrappedObject(), 'array'))->duringReverseTransform($foo);
    }
}
