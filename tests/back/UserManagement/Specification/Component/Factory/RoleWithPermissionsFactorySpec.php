<?php

namespace Specification\Akeneo\UserManagement\Component\Factory;

use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactory;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Akeneo\UserManagement\Component\Factory\RoleWithPermissionsFactory;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use PhpSpec\ObjectBehavior;

class RoleWithPermissionsFactorySpec extends ObjectBehavior
{
    function let(SimpleFactory $roleFactory)
    {
        $this->beConstructedWith($roleFactory);
    }

    function it_is_a_simple_factory()
    {
        $this->shouldImplement(SimpleFactoryInterface::class);
    }

    function it_is_a_role_with_permissions_factory()
    {
        $this->shouldHaveType(RoleWithPermissionsFactory::class);
    }

    function it_creates_a_role_with_permissions(SimpleFactory $roleFactory, RoleInterface $role)
    {
        $roleFactory->create()->shouldBeCalled()->willReturn($role);

        $roleWithPermissions = $this->create();
        $roleWithPermissions->shouldBeAnInstanceOf(RoleWithPermissions::class);
        $roleWithPermissions->role()->shouldReturn($role);
        $roleWithPermissions->permissions()->shouldReturn([]);
    }
}
