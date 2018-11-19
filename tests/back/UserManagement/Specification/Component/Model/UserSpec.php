<?php

namespace Specification\Akeneo\UserManagement\Component\Model;

use Akeneo\UserManagement\Component\Model\User;
use PhpSpec\ObjectBehavior;

class UserSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(User::class);
    }

    function it_has_properties()
    {
        $this->addProperty('propertyName', 'value')->shouldReturn(null);
        $this->getProperty('propertyName')->shouldReturn('value');
    }

    function it_throws_an_exception_if_the_property_does_not_exist()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('getProperty', ['unknown_property']);
    }
}
