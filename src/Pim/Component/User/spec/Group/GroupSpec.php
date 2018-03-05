<?php

namespace spec\Pim\Component\User\Group;

use PhpSpec\ObjectBehavior;
use Pim\Component\User\Group\Group;
use Pim\Component\User\Group\GroupInterface;

class GroupSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('group_name');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Group::class);
    }

    function it_is_a_group()
    {
        $this->shouldImplement(GroupInterface::class);
    }

    function its_roles_are_mutable()
    {
        $user = new \Pim\Component\User\Role\Role('user');
        $admin = new \Pim\Component\User\Role\Role('admin');

        $this->addRole($user);
        $this->addRole($admin);

        $this->removeRole($admin);

        $this->getRole('user')->shouldReturn($user);
        $this->hasRole('admin')->shouldReturn(false);
    }

    function it_returns_its_roles_as_string()
    {
        $user = new \Pim\Component\User\Role\Role('user');
        $admin = new \Pim\Component\User\Role\Role('admin');

        $this->addRole($user);
        $this->addRole($admin);

        $this->getRoleLabelsAsString()->shouldReturn('user, admin');
    }
}
