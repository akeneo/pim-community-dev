<?php

namespace Akeneo\UserManagement\Domain\Permissions\Query;

use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleWithPermissionsRepository;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Akeneo\UserManagement\Component\Repository\RoleRepositoryInterface;
use Akeneo\UserManagement\Domain\Permissions\MinimumEditRolePermission;

class EditRolePermissionsRoleQuery
{
    public function __construct(
        private RoleWithPermissionsRepository $roleWithPermissionsRepository,
        private RoleRepositoryInterface $roleRepository,
    ) {
    }

    /**
     * @return array<RoleInterface>
     */
    public function getRolesWithMinimumEditRolePermissions(): array
    {
        $roles = $this->roleRepository->findAll();
        /** @var RoleInterface[] $minimumPermissionsRoles */
        $minimumPermissionsRoles = [];
        $minimumEditRolePermissions = MinimumEditRolePermission::getAllValues();
        /** @var RoleInterface $role */
        foreach ($roles as $role) {
            $roleWithPermission = $this->roleWithPermissionsRepository->findOneByIdentifier($role->getRole());
            $rolePermissions = $roleWithPermission->permissions();
            $minimumPermissions = array_filter($rolePermissions, function ($permission) use ($rolePermissions, $minimumEditRolePermissions) {
                $isMinimumEditRolePermissions = in_array($permission, $minimumEditRolePermissions);
                return $isMinimumEditRolePermissions && $rolePermissions[$permission];
            }, ARRAY_FILTER_USE_KEY);
            if (count($minimumPermissions) === count($minimumEditRolePermissions)) {
                $minimumPermissionsRoles[] = $role;
            }
        }

        return $minimumPermissionsRoles;
    }

    public function isLastRoleWithEditRolePermissions(string $role)
    {
        $minimumEditRoleRoles = $this->getRolesWithMinimumEditRolePermissions();
        return (count($minimumEditRoleRoles) <= 1 && in_array($role, $minimumEditRoleRoles));
    }
}
