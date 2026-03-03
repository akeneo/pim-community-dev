<?php

namespace Specification\Akeneo\UserManagement\Component\Model;

use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use PhpSpec\ObjectBehavior;

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
}
