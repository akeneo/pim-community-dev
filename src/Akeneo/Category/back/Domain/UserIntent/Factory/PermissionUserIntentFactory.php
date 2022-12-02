<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Category\Domain\UserIntent\Factory;

use Akeneo\Category\Api\Command\UserIntents\AddPermission;
use Akeneo\Category\Api\Command\UserIntents\RemovePermission;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

class PermissionUserIntentFactory implements UserIntentFactory
{
    public function getSupportedFieldNames(): array
    {
        return ['permissions'];
    }

    public function create(string $fieldName, mixed $data): array
    {
        if (false === \is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected($fieldName, static::class, $data);
        }

        $userIntents = [];

        // TODO: remove mock and use the permission finder

        $existingPermissions = [
            'view' => [1, 2, 5],
            'edit' => [1, 2, 5],
            'own' => [1, 2, 5]
        ];

        $addedPermissions = $this->getAddedPermissions($existingPermissions, $data);
        $removedPermissions = $this->getRemovedPermissions($existingPermissions, $data);

        foreach ($addedPermissions as $type => $addedPermissionsPerType) {
            $userIntents[] = new AddPermission($type, $addedPermissionsPerType);
        }

        foreach ($removedPermissions as $type => $removedPermissionsPerType) {
            $userIntents[] = new RemovePermission($type, $removedPermissionsPerType);
        }

        return $userIntents;
    }

    private function getAddedPermissions(array $existingPermissions, array $newPermissions): array
    {
        $addedPermissions = [];

        foreach ($existingPermissions as $type => $existingPermissionsPerType) {
            $addedPermissions[$type] = array_filter($newPermissions[$type], fn ($newPermission) => !in_array($newPermission, $existingPermissionsPerType));
        }

        return $addedPermissions;
    }

    private function getRemovedPermissions(array $existingPermissions, array $newPermissions): array
    {
        $removedPermissions = [];

        foreach ($newPermissions as $type => $newPermissionsPerType) {
            if (is_array($newPermissionsPerType)) {
                $removedPermissions[$type] = array_filter($existingPermissions[$type], fn($existingPermission) => !in_array($existingPermission, $newPermissionsPerType));
            }
        }

        return $removedPermissions;
    }
}
