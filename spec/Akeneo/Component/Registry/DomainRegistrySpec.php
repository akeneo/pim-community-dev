<?php

namespace spec\Akeneo\Component\Registry;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DomainRegistrySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('spec\Akeneo\Component\Registry\RegisteredObjectInterface');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Component\Registry\DomainRegistry');
    }

    function it_is_a_domain_registry()
    {
        $this->shouldImplement('Akeneo\Component\Registry\DomainRegistryInterface');
    }

    function it_throws_an_exception_if_the_alias_already_exists(RegisteredObject $object)
    {
        $this->register('alias', $object);

        $this->shouldThrow('Akeneo\Component\Registry\Exception\ExistingObjectException')
            ->during('register', ['alias', $object]);
    }

    function it_throws_an_exception_if_a_non_object_is_registered()
    {
        $this->shouldThrow('Akeneo\Component\Registry\Exception\InvalidObjectException')
            ->during('register',['alias', 'string']);
    }

    function it_throws_an_exception_if_an_object_which_does_not_implement_the_right_interface(WrongObject $object)
    {
        $this->shouldThrow('Akeneo\Component\Registry\Exception\InvalidObjectException')
            ->during('register', ['alias', $object]);
    }

    function it_checks_is_the_object_is_already_register(RegisteredObject $object)
    {
        $this->register('alias', $object);

        $this->has('alias')->shouldReturn(true);
        $this->has('other_alias')->shouldReturn(false);
    }

    function it_returns_the_object_with_a_specific_alias(RegisteredObject $object)
    {
        $this->register('alias', $object);

        $this->get('alias')->shouldReturn($object);
    }

    function it_throwns_exception_when_an_object_does_not_exist_in_the_registry()
    {
        $this->shouldThrow('Akeneo\Component\Registry\Exception\NonExistingObjectException')
            ->during('get', ['alias']);
    }

    function it_returns_every_registered_object(RegisteredObject $object, RegisteredObject $otherObject)
    {
        $this->all()->shouldReturn([]);

        $this->register('alias', $object);
        $this->register('other_alias', $otherObject);

        $this->all()->shouldReturn([
            'alias' => $object,
            'other_alias' => $otherObject,
        ]);
    }

    function its_registered_objects_are_removable(RegisteredObject $object)
    {
        $this->register('alias', $object);
        $this->unregister('alias')->shouldReturn(null);

        $this->all()->shouldReturn([]);
    }
}

interface RegisteredObjectInterface {}
class RegisteredObject implements RegisteredObjectInterface {}
class WrongObject {}
