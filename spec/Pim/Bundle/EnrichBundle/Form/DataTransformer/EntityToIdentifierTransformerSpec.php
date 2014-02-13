<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\DataTransformer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Doctrine\Common\Persistence\ObjectRepository;

class EntityToIdentifierTransformerSpec extends ObjectBehavior
{
    function let(ObjectRepository $repository, PropertyAccessorInterface $propertyAccessor)
    {
        $this->beConstructedWith($repository, $propertyAccessor);
    }

    function it_is_a_form_data_transformer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Form\DataTransformerInterface');
    }

    function it_transforms_value_into_its_id(\StdClass $entity, $propertyAccessor)
    {
        $propertyAccessor->getValue($entity, 'id')->willReturn(30);

        $this->transform($entity)->shouldReturn(30);
    }

    function it_reverse_transforms_id_into_an_entity(\StdClass $entity, $repository)
    {
        $repository->find(30)->willReturn($entity);

        $this->reverseTransform(30)->shouldReturn($entity);
    }
}
