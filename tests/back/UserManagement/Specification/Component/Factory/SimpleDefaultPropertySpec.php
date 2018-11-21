<?php

namespace Specification\Akeneo\UserManagement\Component\Factory;

use Akeneo\UserManagement\Component\Factory\DefaultProperty;
use Akeneo\UserManagement\Component\Factory\SimpleDefaultProperty;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;

class SimpleDefaultPropertySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('a_property', 'a_default_value');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SimpleDefaultProperty::class);
    }

    function it_is_a_default_property()
    {
        $this->shouldImplement(DefaultProperty::class);
    }

    function it_mutates_the_user()
    {
        $user = new User();

        $this->mutate($user)->shouldReturnAnInstanceOf(UserInterface::class);

        Assert::eq('a_default_value', $user->getProperty('a_property'));
    }
}
