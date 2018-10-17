<?php

namespace Specification\Akeneo\Platform\Bundle\UIBundle\Form\Transformer;

use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class EntityToIdentifierTransformerSpec extends ObjectBehavior
{
    function let(ObjectRepository $repository, PropertyAccessorInterface $propertyAccessor)
    {
        $this->beConstructedWith($repository, false, $propertyAccessor);
    }

    function it_is_a_form_data_transformer()
    {
        $this->shouldImplement('Symfony\Component\Form\DataTransformerInterface');
    }

    function it_transforms_value_into_its_identifier(\StdClass $entity, $propertyAccessor)
    {
        $propertyAccessor->getValue($entity, 'id')->willReturn(30);

        $this->transform($entity)->shouldReturn(30);
    }

    function it_reverse_transforms_identifier_into_an_entity(\StdClass $entity, $repository)
    {
        $repository->findOneBy(['id' => 30])->willReturn($entity);

        $this->reverseTransform(30)->shouldReturn($entity);
    }

    function it_transforms_values_into_identifiers_string(\StdClass $foo, \StdClass $bar, $repository, $propertyAccessor)
    {
        $this->beConstructedWith($repository, true, $propertyAccessor);
        $propertyAccessor->getValue($foo, 'id')->willReturn(4);
        $propertyAccessor->getValue($bar, 'id')->willReturn(8);

        $this->transform([$foo, $bar])->shouldReturn('4,8');
    }

    function it_transforms_values_into_identifiers_array(\StdClass $foo, \StdClass $bar, $repository, $propertyAccessor)
    {
        $this->beConstructedWith($repository, true, $propertyAccessor, null);
        $propertyAccessor->getValue($foo, 'id')->willReturn(4);
        $propertyAccessor->getValue($bar, 'id')->willReturn(8);

        $this->transform([$foo, $bar])->shouldReturn([4, 8]);
    }

    function it_reverse_transforms_identifiers_into_entities(\StdClass $foo, \StdClass $bar, $repository, $propertyAccessor)
    {
        $this->beConstructedWith($repository, true, $propertyAccessor);
        $repository->findBy(['id' => [4, 8]])->willReturn([$foo, $bar]);

        $this->reverseTransform([4, 8])->shouldReturn([$foo, $bar]);
    }

    function it_reverse_transforms_identifiers_into_entities_from_string_with_delimiter(
        \StdClass $foo,
        \StdClass $bar,
        $repository,
        $propertyAccessor
    ) {
        $this->beConstructedWith($repository, true, $propertyAccessor);
        $repository->findBy(['id' => [4, 8]])->willReturn([$foo, $bar]);

        $this->reverseTransform([4, 8])->shouldReturn([$foo, $bar]);
    }

    function it_does_not_transform_null_value()
    {
        $this->transform(null)->shouldReturn(null);
    }

    function it_throws_exception_when_transforming_array_value_in_single_mode()
    {
        $this->shouldThrow(new UnexpectedTypeException(['foo', 'bar'], 'object'))->duringTransform(['foo', 'bar']);
    }

    function it_throws_exception_when_transforming_scalar_value_in_single_mode()
    {
        $this->shouldThrow(new UnexpectedTypeException(15, 'object'))->duringTransform(15);
    }

    function it_throws_exception_when_transforming_scalar_value_in_multiple_mode($repository, $propertyAccessor)
    {
        $this->beConstructedWith($repository, true, $propertyAccessor);
        $this->shouldThrow(new UnexpectedTypeException('foo', 'array'))->duringTransform('foo');
    }

    function it_throws_exception_when_transforming_object_value_in_multiple_mode(
        \StdClass $foo,
        $repository,
        $propertyAccessor
    ) {
        $this->beConstructedWith($repository, true, $propertyAccessor);
        $this->shouldThrow(new UnexpectedTypeException($foo->getWrappedObject(), 'array'))->duringTransform($foo);
    }

    function it_throws_exception_when_reverse_transforming_non_array_value_in_multiple_mode(
        \StdClass $foo,
        $repository,
        $propertyAccessor
    ) {
        $this->beConstructedWith($repository, true, $propertyAccessor);
        $this
            ->shouldThrow(new UnexpectedTypeException($foo->getWrappedObject(), 'array'))
            ->duringReverseTransform($foo);
    }

    function it_throws_exception_when_reverse_transforming_string_value_in_multiple_mode_without_delimiter(
        $repository,
        $propertyAccessor
    ) {
        $this->beConstructedWith($repository, true, $propertyAccessor, null);
        $this->shouldThrow(new UnexpectedTypeException('foo', 'array'))->duringReverseTransform('foo');
    }

    function it_does_not_reverse_transform_null_value()
    {
        $this->reverseTransform(null)->shouldReturn(null);
    }
}
