<?php
declare(strict_types=1);

namespace Specification\Akeneo\UserManagement\Component\Connector;

use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Akeneo\UserManagement\Component\Model\Role;
use PhpSpec\ObjectBehavior;

class RoleWithPermissionsSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('createFromRoleAndPermissionIds', [
            new Role('ROLE_USER'),
            ['id1', 'id2'],
        ]);
    }

    function it_is_instantiable()
    {
        $this->shouldBeAnInstanceOf(RoleWithPermissions::class);
    }

    function it_returns_the_role()
    {
        $this->role()->getRole()->shouldReturn('ROLE_USER');
    }

    function it_returns_alowed_permission_ids()
    {
        $this->allowedPermissionIds()->shouldReturn(['id1', 'id2']);
    }

    function it_cannot_be_constructed_with_bad_permissions_structure()
    {
        $this->beConstructedThrough('createFromRoleAndPermissionIds', [
            new Role('ROLE_USER'),
            ['id1', null],
        ]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
