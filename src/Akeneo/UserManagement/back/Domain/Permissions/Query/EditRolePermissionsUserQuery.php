<?php

namespace Akeneo\UserManagement\Domain\Permissions\Query;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\RoleRepositoryInterface;

class EditRolePermissionsUserQuery
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository,
        private readonly EditRolePermissionsRoleQuery $editRolePermissionsRoleQuery,
    ) {
    }

    /**
     * @return array<UserInterface>
     */
    public function getUsersWithEditRoleRoles(): array
    {
        $minimumPermissionsRoles = $this->editRolePermissionsRoleQuery->getRolesWithMinimumEditRolePermissions();
        $uiUserEnabledByRoles = $this->roleRepository->getUiUserEnabledByRoles($minimumPermissionsRoles);
        return $uiUserEnabledByRoles->getQuery()->execute();
    }

    /**
     * @param array<string> $roles
     */
    public function isLastUserWithEditRolePermissionsRole(array $roles, int $identifier): bool
    {
        $editRoleLeft = $this->getRoleLeftWithEditRolePermissions($roles);
        if (count($editRoleLeft) <= 1) {
            return $this->isUserLeftWithEditRolePermissions($identifier);
        }
        return false;
    }

    public function isLastRoleWithEditRolePermissionsRoleForUser(array $roles, int $identifier): bool
    {
        $editRoleLeft = $this->getRoleLeftWithEditRolePermissions($roles);
        if (count($editRoleLeft) < 1) {
            return $this->isUserLeftWithEditRolePermissions($identifier);
        }
        return false;
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

    /**
     * @param array<string> $roles
     *
     * @return array<string>
     */
    private function getRoleLeftWithEditRolePermissions(array $roles): array
    {
        $editRoleRolesPermissions = $this->editRolePermissionsRoleQuery->getRolesWithMinimumEditRolePermissions();
        $editRoleRolesNamePermissions = array_map(fn ($role) => $role->getRole(), $editRoleRolesPermissions);
        return array_filter($roles, (function ($role) use ($editRoleRolesNamePermissions) {
            return in_array($role, $editRoleRolesNamePermissions);
        }));
    }
}
