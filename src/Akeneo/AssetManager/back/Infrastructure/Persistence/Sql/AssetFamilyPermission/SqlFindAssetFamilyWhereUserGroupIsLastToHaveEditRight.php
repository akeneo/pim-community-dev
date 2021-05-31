<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamilyPermission;

use Doctrine\DBAL\Connection;
use PDO;

/**
 * This query finds the Asset Family identifiers for which the given user group is the last one
 * to have the edit permission on.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class SqlFindAssetFamilyWhereUserGroupIsLastToHaveEditRight
{
    private Connection $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function find(int $userGroupId): array
    {
        $sql = "SELECT perm1.asset_family_identifier, COUNT(*) as cartesian_product
                FROM akeneo_asset_manager_asset_family_permissions perm1
                INNER JOIN akeneo_asset_manager_asset_family_permissions perm2
                    ON perm1.asset_family_identifier = perm2.asset_family_identifier
                    AND perm1.right_level = perm2.right_level
                    AND perm1.right_level = 'edit'
                    AND perm1.user_group_identifier = :userGroupIdentifier
                GROUP BY perm1.asset_family_identifier
                HAVING cartesian_product = 1;
        ";

        $statement = $this->sqlConnection->executeQuery(
            $sql,
            ['userGroupIdentifier' => $userGroupId],
            ['userGroupId' => PDO::PARAM_INT]
        );

        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }
}
