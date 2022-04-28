<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category;

use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 */
class GetUserGroupRootCategoriesAccesses
{
    private Connection $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    /**
     * @return array{
     *     own: array{
     *         all: bool,
     *         identifiers: string[]
     *     },
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
            'own' => [
                'all' => false,
                'identifiers' => [],
            ],
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
     *     own: array{
     *         all: bool,
     *         identifiers: string[]
     *     },
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

            $permissions['own']['all'] = $defaultPermissions['category_own'] ?? false;
            $permissions['edit']['all'] = $defaultPermissions['category_edit'] ?? false;
            $permissions['view']['all'] = $defaultPermissions['category_view'] ?? false;
        }
    }

    /**
     * @param array{
     *     own: array{
     *         all: bool,
     *         identifiers: string[]
     *     },
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
       pim_catalog_category.code, 
       view_items AS view, 
       edit_items AS edit, 
       own_items AS own
FROM pimee_security_product_category_access
JOIN pim_catalog_category ON pim_catalog_category.id = pimee_security_product_category_access.category_id
JOIN oro_access_group ON oro_access_group.id = pimee_security_product_category_access.user_group_id
WHERE oro_access_group.name = :user_group_name
AND pim_catalog_category.parent_id IS NULL
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
