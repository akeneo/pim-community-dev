<?php
declare(strict_types=1);

namespace Specification\Akeneo\UserManagement\Component\Connector;

use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use PhpSpec\ObjectBehavior;

class RoleWithPermissionsSpec extends ObjectBehavior
{
    function it_is_instantiable(RoleInterface $role)
    {
        $this->beConstructedThrough('createFromRoleAndPrivileges', [$role, []]);
        $this->shouldBeAnInstanceOf(RoleWithPermissions::class);
    }

    function it_returns_the_role(RoleInterface $role)
    {
        $this->beConstructedThrough('createFromRoleAndPrivileges', [$role, []]);
        $this->role()->shouldBe($role);
    }

    function it_returns_privileges(RoleInterface $role, AclPrivilege $privilege1, AClPrivilege $privilege2)
    {
        $this->beConstructedThrough('createFromRoleAndPrivileges', [$role, [$privilege1, $privilege2]]);
        $this->privileges()->shouldReturn([$privilege1, $privilege2]);
    }

    function it_cannot_be_constructed_with_bad_permissions_structure()
    {
        $this->beConstructedThrough('createFromRoleAndPrivileges', [
            new Role('ROLE_USER'),
            ['id1', null],
        ]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
