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
        $this->getProperty('property_name')->shouldReturn('value');
    }
}
