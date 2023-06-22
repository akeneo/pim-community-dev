<?php

namespace Akeneo\UserManagement\Domain\Permissions;

use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleRepository;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleWithPermissionsRepository;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;

class CheckEditRolePermissions
{
    public const MINIMUM_EDITROLE_PRIVILEGES = ['action:pim_user_role_edit','action:pim_user_role_index', 'action:oro_config_system'];
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
        $minimumAdminPrivileges = self::MINIMUM_EDITROLE_PRIVILEGES;
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

    /**
     * @return array<UserInterface>
     */
    public function getUsersWithEditRoleRoles(): array
    {
        $minimumPrivilegesRoles = $this->getRolesWithMinimumEditRolePrivileges();
        $uiUserEnabledByRoles = $this->roleRepository->getUiUserEnabledByRoles($minimumPrivilegesRoles);
        return $uiUserEnabledByRoles->getQuery()->execute();
    }

    /**
     * @param array<string> $roles
     */
    public function isLastUserWithEditPrivilegeRole(array $roles, int $identifier): bool
    {
        $editRoleLeft = $this->getRoleLeftWithEditRolePermissions($roles);
        if (count($editRoleLeft) <= 1) {
            return $this->isUserLeftWithEditRolePermissions($identifier);
        }
        return false;
    }

    public function isLastRoleWithEditPrivilegeRoleForUser(array $roles, int $identifier): bool
    {
        $editRoleLeft = $this->getRoleLeftWithEditRolePermissions($roles);
        if (count($editRoleLeft) < 1) {
            return $this->isUserLeftWithEditRolePermissions($identifier);
        }
        return false;
    }

    /**
     * @param array<string> $roles
     *
     * @return array<string>
     */
    private function getRoleLeftWithEditRolePermissions(array $roles): array
    {
        $editRoleRolesPrivileges = $this->getRolesWithMinimumEditRolePrivileges();
        $editRoleRolesNamePrivileges = array_map(fn ($role) => $role->getRole(), $editRoleRolesPrivileges);
        return array_filter($roles, (function ($role) use ($editRoleRolesNamePrivileges) {
            return in_array($role, $editRoleRolesNamePrivileges);
        }));
    }

    private function isUserLeftWithEditRolePermissions(int $identifier): bool
    {
        $usersWithEditRoleRoles = $this->getUsersWithEditRoleRoles();
        if (count($usersWithEditRoleRoles) <= 1) {
            $lastUser = $usersWithEditRoleRoles[0] ?? null;
            return $lastUser && $lastUser->getId() === $identifier;
        }
        return false;
    }
}
