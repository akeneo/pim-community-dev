<?php

namespace Specification\Akeneo\UserManagement\Component\Model;

use Akeneo\UserManagement\Component\Model\Role;
use PhpSpec\ObjectBehavior;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Prophecy\Argument;

class RoleSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('role_name');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Role::class);
    }

    function it_is_a_role()
    {
        $this->shouldImplement(RoleInterface::class);
    }

    function its_label_is_the_role_by_default()
    {
        $this->getLabel()->shouldReturn('role_name');
    }

    function its_role_should_be_prefixed()
    {
        $this->setRole('user');
        $this->getRole()->shouldReturn('ROLE_USER');
    }
}
