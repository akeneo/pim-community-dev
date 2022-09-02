<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Persistence\ORM\AttributeGroup;

use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 */
class GetUserGroupAttributeGroupsAccesses
{
    private Connection $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    /**
     * @return array{
     *     edit: array{
     *         all: bool,
     *         identifiers: string[]
     *     },
     *     view: array{
     *         all: bool,
     *         identifiers: string[]
     *     }
     * }
     */
    public function execute(string $userGroupName): array
    {
        $permissions = [
            'edit' => [
                'all' => false,
                'identifiers' => [],
            ],
            'view' => [
                'all' => false,
                'identifiers' => [],
            ],
        ];

        $this->hydratePermissionsAllFlag($permissions, $userGroupName);
        $this->hydratePermissionsIdentifiers($permissions, $userGroupName);

        return $permissions;
    }

    /**
     * @param array{
     *     edit: array{
     *         all: bool,
     *         identifiers: string[]
     *     },
     *     view: array{
     *         all: bool,
     *         identifiers: string[]
     *     }
     * } $permissions
     */
    private function hydratePermissionsAllFlag(array &$permissions, string $userGroupName)
    {
        $query = <<<SQL
SELECT default_permissions
FROM oro_access_group
WHERE name = :user_group_name
LIMIT 1
SQL;

        $row = $this->connection->fetchAssociative($query, [
            'user_group_name' => $userGroupName,
        ]);

        if (false !== $row && null !== $row['default_permissions']) {
            $defaultPermissions = \json_decode($row['default_permissions'], true);

            $permissions['edit']['all'] = $defaultPermissions['attribute_group_edit'] ?? false;
            $permissions['view']['all'] = $defaultPermissions['attribute_group_view'] ?? false;
        }
    }

    /**
     * @param array{
     *     edit: array{
     *         all: bool,
     *         identifiers: string[]
     *     },
     *     view: array{
     *         all: bool,
     *         identifiers: string[]
     *     }
     * } $permissions
     */
    private function hydratePermissionsIdentifiers(array &$permissions, string $userGroupName)
    {
        $query = <<<SQL
SELECT 
       pim_catalog_attribute_group.code, 
       view_attributes AS view, 
       edit_attributes AS edit
FROM pimee_security_attribute_group_access
JOIN pim_catalog_attribute_group ON pim_catalog_attribute_group.id = pimee_security_attribute_group_access.attribute_group_id
JOIN oro_access_group ON oro_access_group.id = pimee_security_attribute_group_access.user_group_id
WHERE oro_access_group.name = :user_group_name
SQL;

        $rows = $this->connection->fetchAllAssociative($query, [
            'user_group_name' => $userGroupName,
        ]) ?: [];

        foreach ($permissions as $permissionLevel => $permission) {
            if (true === $permission['all']) {
                continue;
            }

            foreach ($rows as $row) {
                if ('1' === $row[$permissionLevel]) {
                    $permissions[$permissionLevel]['identifiers'][] = $row['code'];
                }
            }
        }
    }
}
