<?php

namespace spec\Pim\Component\User\Model;

use Pim\Component\User\Model\Group;
use PhpSpec\ObjectBehavior;
use Pim\Component\User\Model\GroupInterface;
use Pim\Component\User\Model\Role;
use Prophecy\Argument;

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
        $user = new Role('user');
        $admin = new Role('admin');

        $this->addRole($user);
        $this->addRole($admin);

        $this->removeRole(new Role('admin'));

        $this->getRole('user')->shouldReturn($user);
        $this->hasRole('admin')->shouldReturn(false);
    }

    function it_returns_its_roles_as_string()
    {
        $user = new Role('user');
        $admin = new Role('admin');

        $this->addRole($user);
        $this->addRole($admin);

        $this->getRoleLabelsAsString()->shouldReturn('user, admin');
    }
}
