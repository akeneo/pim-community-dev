<?php

namespace Akeneo\Category\Domain\ValueObject;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class PermissionCollection
{
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const OWN = 'own';

    /** @var array<string, array<int>> */
    private array $removedUserGroupIdsFromPermissions;

    // @phpstan-ignore-next-line
    private function __construct(private ?array $permissions)
    {
    }

    /**
     * @param array<string, array<array{id: int, label: string}>>|null $permissions
     */
    public static function fromArray(?array $permissions): self
    {
        return new self($permissions);
    }

    /**
     * @param array<array{id: int, label: string}> $newUserGroups
     */
    public function addUserGroupsToPermission(string $permission, array $newUserGroups): self
    {
        if (array_key_exists($permission, $this->permissions)) {
            $existingUserGroupsIds = $this->getUserGroupIdsPerPermission()[$permission];

            foreach ($newUserGroups as $newUserGroup) {
                if (array_key_exists('id', $newUserGroup) && !in_array($newUserGroup['id'], $existingUserGroupsIds)) {
                    $this->permissions[$permission][] = $newUserGroup;
                }
            }
        } else {
            $this->permissions[$permission] = $newUserGroups;
        }

        return new self($this->permissions);
    }

    /**
     * @param array<array{id: int, label: string}> $userGroupsToRemove
     */
    public function removeUserGroupsFromPermission(string $permission, array $userGroupsToRemove): self
    {
        if (array_key_exists($permission, $this->permissions)) {
            $existingUserGroupsIds = $this->getUserGroupIdsPerPermission()[$permission];

            foreach ($userGroupsToRemove as $userGroupToRemove) {
                if (array_key_exists('id', $userGroupToRemove) && ($key = array_search($userGroupToRemove['id'], $existingUserGroupsIds)) !== false) {
                    $this->removedUserGroupIdsFromPermissions[$permission][] = $userGroupToRemove['id'];
                    unset($this->permissions[$permission][$key]);
                }
            }
        }

        return new self($this->permissions);
    }

    /** @return array<array{id: int, label: string}> */
    public function getViewUserGroups(): array
    {
        return $this->permissions[self::VIEW] ?? [];
    }

    /** @return array<array{id: int, label: string}> */
    public function getEditUserGroups(): array
    {
        return $this->permissions[self::EDIT] ?? [];
    }

    /** @return array<array{id: int, label: string}> */
    public function getOwnUserGroups(): array
    {
        return $this->permissions[self::OWN] ?? [];
    }

    /** @return array<string, array<int>> */
    public function getUserGroupIdsPerPermission(): array
    {
        return [
            self::VIEW => array_map(fn ($permission) => $permission['id'], $this->getViewUserGroups()),
            self::EDIT => array_map(fn ($permission) => $permission['id'], $this->getEditUserGroups()),
            self::OWN => array_map(fn ($permission) => $permission['id'], $this->getOwnUserGroups()),
        ];
    }

    /** @return array<string, array<int>> */
    public function getRemovedUserGroupIdsFromPermissions(): array
    {
        if (!isset($this->removedUserGroupIdsFromPermissions)) {
            return [
                self::VIEW => [],
                self::EDIT => [],
                self::OWN => [],
            ];
        }

        return $this->removedUserGroupIdsFromPermissions;
    }

    /** @return array<string, array<array{id: int, label: string}>>|null */
    public function normalize(): ?array
    {
        return $this->permissions;
    }
}
