<?php

declare(strict_types=1);

namespace Akeneo\Category\Api\Model;

/**
 * A Permissions object represents the permissions granted for a category.
 * Permissions can be one of {'view', 'edit', 'own'}
 * Granted entities are represented by group IDs.
 *
 * @phpstan-type Group int
 * @phpstan-type Groups Group[]
 * @phpstan-type PermissionCode 'view'|'edit'|'own'
 * @phpstan-type PermissionsMap array<PermissionCode, Groups>
 *
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Permissions
{
    /**
     * @param PermissionsMap $permissions
     */
    public function __construct(private array $permissions)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function normalize(): array
    {
        return $this->permissions;
    }
}
