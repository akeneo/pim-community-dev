<?php

namespace Specification\Akeneo\Platform\Bundle\UIBundle\Form\Transformer;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

class IdentifiableModelTransformerSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $repository, \stdClass $entity)
    {
        $entity->code = 'foo';
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier('foo')->willReturn($entity);
        $this->beConstructedWith($repository, ['multiple' => false]);
    }

    function it_is_a_form_data_transformer()
    {
        $this->shouldImplement('Symfony\Component\Form\DataTransformerInterface');
    }

    function it_transforms_model_into_its_identifier($entity)
    {
        $this->transform($entity)->shouldReturn('foo');
    }

    function it_reverse_transforms_identifier_into_a_model(\stdClass $entity)
    {
        $this->reverseTransform('foo')->shouldReturn($entity);
    }

    function it_cannot_transform_models_with_composite_identifier(\stdClass $foo, $repository)
    {
        $repository->getIdentifierProperties()->willReturn(['code', 'locale']);

        $this->shouldThrow(new \InvalidArgumentException("Cannot transform object with multiple identifiers"))
            ->duringTransform($foo);
    }

    function it_transforms_models_into_codes_array(
        \stdClass $foo,
        \stdClass $bar,
        $repository
    ) {
        $this->beConstructedWith($repository, ['multiple' => true]);

        $foo->code = 'fooCode';
        $bar->code = 'barCode';

        $this->transform([$foo, $bar])->shouldReturn(['fooCode', 'barCode']);
    }

    function it_reverse_transforms_codes_into_entities(
        \stdClass $foo,
        \stdClass $bar,
        $repository
    ) {
        $this->beConstructedWith($repository, ['multiple' => true]);

        $foo->code = 'fooCode';
        $bar->code = 'barCode';

        $repository->findOneByIdentifier('fooCode')->willReturn($foo);
        $repository->findOneByIdentifier('barCode')->willReturn($bar);

        $this->reverseTransform(['fooCode', 'barCode'])->shouldReturn([$foo, $bar]);
    }

    function it_does_not_transform_null_value()
    {
        $this->transform(null)->shouldReturn(null);
    }

    function it_throws_exception_when_transforming_array_value_in_single_mode()
    {
        $formData = ['fooCode', 'barCode'];
        $this->shouldThrow(new UnexpectedTypeException($formData, 'object'))
            ->duringTransform($formData);
    }

    function it_throws_exception_when_transforming_scalar_value_in_single_mode()
    {
        $this->shouldThrow(new UnexpectedTypeException(15, 'object'))
            ->duringTransform(15);
    }

    function it_throws_exception_when_transforming_scalar_value_in_multiple_mode($repository)
    {
        $this->beConstructedWith($repository, ['multiple' => true]);
        $this->shouldThrow(new UnexpectedTypeException('foo', 'array'))
            ->duringTransform('foo');
    }

    function it_throws_exception_when_transforming_object_value_in_multiple_mode(
        \stdClass $foo,
        $repository
    ) {
        $this->beConstructedWith($repository, ['multiple' => true]);
        $this->shouldThrow(new UnexpectedTypeException($foo->getWrappedObject(), 'array'))
            ->duringTransform($foo);
    }

    function it_throws_exception_when_reverse_transforming_non_array_value_in_multiple_mode(
        \stdClass $foo,
        $repository
    ) {
        $this->beConstructedWith($repository, ['multiple' => true]);
        $this
            ->shouldThrow(new UnexpectedTypeException($foo->getWrappedObject(), 'array'))
            ->duringReverseTransform($foo);
    }

    function it_does_not_reverse_transform_null_value()
    {
        $this->reverseTransform(null)->shouldReturn(null);
    }
}
