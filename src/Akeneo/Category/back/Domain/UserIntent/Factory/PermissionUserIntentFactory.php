<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace AkeneoEnterprise\Category\Domain\UserIntent\Factory;

use Akeneo\Category\Domain\UserIntent\Factory\UserIntentFactory;
use Akeneo\Pim\Permission\Bundle\ServiceApi\Category\GetCategoryProductPermissionsByCategoryIdInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use AkeneoEnterprise\Category\Api\Command\UserIntents\AddPermission;
use AkeneoEnterprise\Category\Api\Command\UserIntents\RemovePermission;

class PermissionUserIntentFactory implements UserIntentFactory
{
    public function __construct(
        private readonly GetCategoryProductPermissionsByCategoryIdInterface $getCategoryProductPermissionsByCategoryId,
    ) {
    }

    public function getSupportedFieldNames(): array
    {
        return ['permissions'];
    }

    public function create(string $fieldName, int $categoryId, mixed $data): array
    {
        if (false === \is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected($fieldName, static::class, $data);
        }

        $userIntents = [];

        $existingUserGroupsPerPermission = ($this->getCategoryProductPermissionsByCategoryId)($categoryId);

        $addedUserGroupsPerPermission = $this->getAddedUserGroupsPerPermission($existingUserGroupsPerPermission, $data);
        $removedUserGroupsPerPermission = $this->getRemovedUserGroupsPerPermission($existingUserGroupsPerPermission, $data);

        foreach ($addedUserGroupsPerPermission as $permission => $addedUserGroups) {
            if ($addedUserGroups) {
                $userIntents[] = new AddPermission($permission, $addedUserGroups);
            }
        }

        foreach ($removedUserGroupsPerPermission as $permission => $removedUserGroups) {
            if ($removedUserGroups) {
                $userIntents[] = new RemovePermission($permission, $removedUserGroups);
            }
        }

        return $userIntents;
    }

    /**
     * @param array<string, array<array{id: int, label: string}>> $existingUserGroupsPerPermission
     * @param array<string, array<array{id: int, label: string}>> $newUserGroupsPerPermission
     *
     * @return array<string, array<array{id: int, label: string}>>
     */
    private function getAddedUserGroupsPerPermission(array $existingUserGroupsPerPermission, array $newUserGroupsPerPermission): array
    {
        $addedUserGroupsPerPermission = [];

        foreach ($existingUserGroupsPerPermission as $permission => $existingUserGroups) {
            $existingUserGroupsIds = array_map(fn ($existingUserGroup) => $existingUserGroup['id'], $existingUserGroups);
            if (isset($newUserGroupsPerPermission[$permission])) {
                $addedUserGroupsPerPermission[$permission] = array_values(
                    array_filter($newUserGroupsPerPermission[$permission], fn ($newUserGroup) => !in_array($newUserGroup['id'], $existingUserGroupsIds)),
                );
            }
        }

        return $addedUserGroupsPerPermission;
    }

    /**
     * @param array<string, array<array{id: int, label: string}>> $existingUserGroupsPerPermission
     * @param array<string, array<array{id: int, label: string}>> $newUserGroupsPerPermission
     *
     * @return array<string, array<array{id: int, label: string}>>
     */
    private function getRemovedUserGroupsPerPermission(array $existingUserGroupsPerPermission, array $newUserGroupsPerPermission): array
    {
        $removedUserGroupsPerPermission = [];

        foreach ($newUserGroupsPerPermission as $permission => $newUserGroups) {
            if (is_array($newUserGroups)) {
                $newUserGroupsIds = array_map(fn ($newUserGroup) => $newUserGroup['id'], $newUserGroups);
                if (isset($existingUserGroupsPerPermission[$permission])) {
                    $removedUserGroupsPerPermission[$permission] = array_values(
                        array_filter($existingUserGroupsPerPermission[$permission], fn ($existingUserGroup) => !in_array($existingUserGroup['id'], $newUserGroupsIds)),
                    );
                }
            }
        }

        return $removedUserGroupsPerPermission;
    }
}
