<?php

namespace spec\Akeneo\Component\Registry;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PrioritizedDomainRegistrySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('spec\Akeneo\Component\Registry\PrioritizedRegisteredObjectInterface');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Component\Registry\PrioritizedDomainRegistry');
    }

    function it_is_a_domain_registry()
    {
        $this->shouldImplement('Akeneo\Component\Registry\PrioritizedDomainRegistryInterface');
    }

    function it_throws_an_exception_if_a_non_object_is_registered()
    {
        $this->shouldThrow('Akeneo\Component\Registry\Exception\InvalidObjectException')
            ->during('register',[42, 'string']);
    }

    function it_throws_an_exception_if_an_object_which_does_not_implement_the_right_interface(PrioritizedWrongObject $object)
    {
        $this->shouldThrow('Akeneo\Component\Registry\Exception\InvalidObjectException')
            ->during('register',[42, $object]);
    }

    function it_returns_every_registered_object(
        PrioritizedRegisteredObject $object,
        PrioritizedRegisteredObject $otherObject,
        PrioritizedRegisteredObject $lastOtherObject)
    {
        $registeredObjects = $this->all();
        $registeredObjects->shouldHaveType('\SplPriorityQueue');
        $registeredObjects->shouldHaveCount(0);

        $this->register(42, $object);
        $this->register(42, $otherObject);
        $this->register(42, $lastOtherObject);

        $registeredObjects = $this->all();
        $registeredObjects->shouldHaveType('\SplPriorityQueue');
        $registeredObjects->shouldHaveCount(3);
        $registeredObjects->top()->shouldReturn($object);
        $registeredObjects->next();
        $registeredObjects->current()->shouldReturn($otherObject);
        $registeredObjects->next();
        $registeredObjects->current()->shouldReturn($lastOtherObject);
    }

    function its_registered_objects_are_removable(
        PrioritizedRegisteredObject $object,
        PrioritizedRegisteredObject $otherRegisteredObject
    ) {
        $this->register(42, $object);
        $this->register(10, $otherRegisteredObject);
        $this->unregister($object);

        $registeredObjects = $this->all();
        $registeredObjects->shouldHaveType('\SplPriorityQueue');
        $registeredObjects->shouldHaveCount(1);
    }
}

interface PrioritizedRegisteredObjectInterface {}
class PrioritizedRegisteredObject implements PrioritizedRegisteredObjectInterface {}
class PrioritizedWrongObject {}
