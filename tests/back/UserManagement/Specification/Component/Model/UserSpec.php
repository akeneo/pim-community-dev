<?php

namespace Specification\Akeneo\UserManagement\Component\Model;

use Akeneo\UserManagement\Component\Model\User;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UserSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(User::class);
    }

    function it_has_properties()
    {
        $this->addProperties('propertyName', 'value')->shouldReturn(null);
        $this->get('propertyName')->shouldReturn('value');
    }
}
