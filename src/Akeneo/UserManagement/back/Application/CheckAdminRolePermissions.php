<?php

namespace Akeneo\UserManagement\Application;

use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleRepository;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleWithPermissionsRepository;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;

class CheckAdminRolePermissions
{
    public function __construct(
        private RoleWithPermissionsRepository $roleWithPermissionsRepository,
        private RoleRepository $roleRepository,
    ) {
    }

    /**
     * @return array<RoleInterface>
     */
    public function getRolesWithMinimumAdminPrivileges(): array
    {
        $roles = $this->roleRepository->findAll();
        /** @var RoleInterface[] $minimumPrivilegesRoles */
        $minimumPrivilegesRoles = [];
        $minimumAdminPrivileges = ['action:pim_user_role_edit','action:pim_user_role_index', 'action:oro_config_system'];
        /** @var RoleInterface $role */
        foreach ($roles as $role) {
            $roleWithPermission = $this->roleWithPermissionsRepository->findOneByIdentifier($role->getRole());
            $rolePermissions = $roleWithPermission->permissions();
            $minimumPrivileges = array_filter($rolePermissions, function ($permission) use ($rolePermissions, $minimumAdminPrivileges) {
                $isMinimumAdminPrivileges = in_array($permission, $minimumAdminPrivileges);
                return $isMinimumAdminPrivileges && $rolePermissions[$permission];
            }, ARRAY_FILTER_USE_KEY);
            if(count($minimumPrivileges) === count($minimumAdminPrivileges)) {
                $minimumPrivilegesRoles[] = $role;
            }
        }

        return $minimumPrivilegesRoles;
    }

    /**
     * @return array<UserInterface>
     */
    public function getUsersWithAdminRoles(): array
    {
        $minimumPrivilegesRoles = $this->getRolesWithMinimumAdminPrivileges();
        $uiUserEnabledByRoles = $this->roleRepository->getUiUserEnabledByRoles($minimumPrivilegesRoles);
        return $uiUserEnabledByRoles->getQuery()->execute();
    }
}
