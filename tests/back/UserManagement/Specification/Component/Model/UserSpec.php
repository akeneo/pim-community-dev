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

    function it_initializes_the_user_as_a_non_app()
    {
        $this->isApiUser()->shouldReturn(false);
    }

    function it_defines_the_user_as_a_user_app()
    {
        $this->defineAsApiUser();
        $this->isApiUser()->shouldReturn(true);
    }
}
