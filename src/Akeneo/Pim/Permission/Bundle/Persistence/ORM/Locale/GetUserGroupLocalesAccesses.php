<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Persistence\ORM\Locale;

use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 */
class GetUserGroupLocalesAccesses
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

            $permissions['edit']['all'] = $defaultPermissions['locale_edit'] ?? false;
            $permissions['view']['all'] = $defaultPermissions['locale_view'] ?? false;
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
       pim_catalog_locale.code, 
       view_products AS view, 
       edit_products AS edit
FROM pimee_security_locale_access
JOIN pim_catalog_locale ON pim_catalog_locale.id = pimee_security_locale_access.locale_id
JOIN oro_access_group ON oro_access_group.id = pimee_security_locale_access.user_group_id
WHERE oro_access_group.name = :user_group_name
    AND pim_catalog_locale.is_activated = 1
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
