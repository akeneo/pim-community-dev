<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Bundle\Doctrine\ORM\Query;

use Doctrine\DBAL\Connection;

class FetchUserRightsOnAsset
{
    /** @var Connection */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fetch(string $assetIdentifier, int $userId): UserRightsOnAsset
    {
        $sql = <<<SQL
            SELECT 
                asset_categories.asset_identifier, 
                COALESCE(SUM(access.edit_items), 0) as count_editable_categories
            FROM
                (
                    SELECT
                        p.code as asset_identifier, cp.category_id
                    FROM 
                        pimee_product_asset_asset p
                        LEFT JOIN pimee_product_asset_asset_category cp ON cp.asset_id = p.id
                        WHERE p.code = :asset_code
                ) as asset_categories
                LEFT JOIN 
                (
                    SELECT
                        pca.category_id,
                        pca.edit_items
                    FROM pimee_security_asset_category_access pca
                    JOIN oro_access_group ag ON pca.user_group_id = ag.id
                    JOIN oro_user_access_group uag ON uag.group_id = ag.id AND uag.user_id = :user_id
                ) access ON access.category_id = asset_categories.category_id
            GROUP BY 
                asset_categories.asset_identifier
SQL;

        $result = $this->connection->executeQuery(
            $sql,
            ['asset_code' => $assetIdentifier, 'user_id' =>$userId]
        )->fetch();

        $userRightsOnAsset = new UserRightsOnAsset(
            $assetIdentifier,
            $userId,
            (int) $result['count_editable_categories']
        );

        return $userRightsOnAsset;
    }
}
