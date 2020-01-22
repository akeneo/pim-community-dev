<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\InMemory\Repository;

final class InMemoryUserPermissionsRepository
{
    private $roles = [
        0 => 'ROLE_USER',
        1 => 'ROLE_API'
    ];

    private $groups = [
        2 => 'All',
        3 => 'API'
    ];

    private $userRoles = [];

    private $userGroups = [];

    /**
     * @throws \InvalidArgumentException
     */
    public function getRoleIdByIdentifier(string $identifier): int
    {
        foreach ($this->roles as $id => $role) {
            if ($role === $identifier) {
                return $id;
            }
        }

        throw new \InvalidArgumentException(sprintf('Role `%s` not found', $identifier));
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function getGroupIdByIdentifier(string $identifier): int
    {
        foreach ($this->groups as $id => $group) {
            if ($group === $identifier) {
                return $id;
            }
        }

        throw new \InvalidArgumentException(sprintf('Group `%s` not found', $identifier));
    }

    public function setUserPermissions(int $userId, int $roleId, int $groupId): void
    {
        $this->userRoles[$userId] = $roleId;
        $this->userGroups[$userId] = $groupId;
    }

    public function getUserRole(int $userId): string
    {
        return $this->roles[$this->userRoles[$userId]];
    }

    public function getUserGroup(int $userId): string
    {
        return $this->groups[$this->userGroups[$userId]];
    }
}
