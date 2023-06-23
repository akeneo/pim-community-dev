<?php

namespace Akeneo\UserManagement\Domain\Permissions;

use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleRepository;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleWithPermissionsRepository;
use Akeneo\UserManagement\Component\Model\RoleInterface;

class EditRolePermissionsRoleRepository
{
    public function __construct(
        private RoleWithPermissionsRepository $roleWithPermissionsRepository,
        private RoleRepository $roleRepository,
    ) {
    }

    /**
     * @return array<RoleInterface>
     */
    public function getRolesWithMinimumEditRolePrivileges(): array
    {
        $roles = $this->roleRepository->findAll();
        /** @var RoleInterface[] $minimumPrivilegesRoles */
        $minimumPrivilegesRoles = [];
        $minimumAdminPrivileges = MinimumEditRolePermission::getAllValues();
        /** @var RoleInterface $role */
        foreach ($roles as $role) {
            $roleWithPermission = $this->roleWithPermissionsRepository->findOneByIdentifier($role->getRole());
            $rolePermissions = $roleWithPermission->permissions();
            $minimumPrivileges = array_filter($rolePermissions, function ($permission) use ($rolePermissions, $minimumAdminPrivileges) {
                $isMinimumAdminPrivileges = in_array($permission, $minimumAdminPrivileges);
                return $isMinimumAdminPrivileges && $rolePermissions[$permission];
            }, ARRAY_FILTER_USE_KEY);
            if (count($minimumPrivileges) === count($minimumAdminPrivileges)) {
                $minimumPrivilegesRoles[] = $role;
            }
        }

        return $minimumPrivilegesRoles;
    }
}
