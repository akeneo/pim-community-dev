<?php

namespace Specification\Akeneo\UserManagement\Component\Model;

use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Akeneo\UserManagement\Component\Model\Role;
use PhpSpec\ObjectBehavior;

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

        $this->removeRole($admin);

        $this->getRole('user')->shouldReturn($user);
        $this->hasRole('admin')->shouldReturn(false);
    }

    function it_returns_its_roles_as_string()
    {
        $user = new Role('ROLE_USER');
        $user->setLabel('user');

        $admin = new Role('ROLE_ADMIN');
        $admin->setLabel('admin');

        $this->addRole($user);
        $this->addRole($admin);

        $this->getRoleLabelsAsString()->shouldReturn('user, admin');
    }

    public function it_can_set_a_default_permission()
    {
        $this->setDefaultPermission('foo', true);
        $this->getDefaultPermissions()->shouldReturn([
            'foo' => true,
        ]);
    }

    public function it_has_a_default_type(): void
    {
        $this->getType()->shouldReturn('default');
    }

    public function it_changes_the_default_type(): void
    {
        $this->setType('anything_else');
        $this->getType()->shouldReturn('anything_else');
    }
}
