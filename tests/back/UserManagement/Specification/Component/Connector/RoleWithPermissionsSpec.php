<?php
declare(strict_types=1);

namespace Specification\Akeneo\UserManagement\Component\Connector;

use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use PhpSpec\ObjectBehavior;

class RoleWithPermissionsSpec extends ObjectBehavior
{
    function it_is_instantiable(RoleInterface $role)
    {
        $this->beConstructedThrough('createFromRoleAndPermissions', [$role, []]);
        $this->shouldBeAnInstanceOf(RoleWithPermissions::class);
    }

    function it_returns_the_role(RoleInterface $role)
    {
        $this->beConstructedThrough('createFromRoleAndPermissions', [$role, []]);
        $this->role()->shouldBe($role);
    }

    function it_returns_permissions(RoleInterface $role)
    {
        $this->beConstructedThrough(
            'createFromRoleAndPermissions',
            [$role, ['action:privilege1' => true, 'action:privilege2' => false]]
        );
        $this->permissions()->shouldReturn(['action:privilege1' => true, 'action:privilege2' => false]);
    }

    function it_cannot_be_constructed_with_non_string_permission_keys()
    {
        $this->beConstructedThrough('createFromRoleAndPermissions', [
            new Role('ROLE_USER'),
            [0 => true],
        ]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_constructed_with_non_boolean_permission_values()
    {
        $this->beConstructedThrough(
            'createFromRoleAndPermissions',
            [
                new Role('ROLE_USER'),
                ['action:privilege1' => 1],
            ]
        );
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function its_permissions_can_be_mutated()
    {
        $this->beConstructedThrough(
            'createFromRoleAndPermissions',
            [
                new Role('ROLE_USER'),
                ['action:privilege1' => true],
            ]
        );
        $this->permissions()->shouldReturn(['action:privilege1' => true]);

        $this->setPermissions(['action:privilege1' => false, 'action:privilege2' => true]);
        $this->permissions()->shouldReturn(['action:privilege1' => false, 'action:privilege2' => true]);
    }
}
