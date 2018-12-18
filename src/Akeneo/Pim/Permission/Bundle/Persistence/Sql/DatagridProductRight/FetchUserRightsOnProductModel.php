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

namespace Akeneo\Pim\Permission\Bundle\Persistence\Sql\DatagridProductRight;

use Akeneo\Pim\Permission\Component\Authorization\Model\UserRightsOnProductModel;
use Doctrine\DBAL\Connection;

class FetchUserRightsOnProductModel
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

    public function fetch(string $productModelCode, int $userId): UserRightsOnProductModel
    {
        $sql = <<<SQL
            SELECT 
                product_model_categories.product_model_code, 
                COALESCE(SUM(access.edit_items), 0) as count_editable_categories, 
                COALESCE(SUM(access.own_items), 0) as count_ownable_categories,
                COUNT(product_model_categories.category_id) as number_categories
            FROM
                (
                    SELECT
                        pm.code as product_model_code, cp.category_id
                    FROM 
                        pim_catalog_product_model pm
                        LEFT JOIN pim_catalog_category_product_model cp ON cp.product_model_id = pm.id
                        WHERE pm.code = :code
                    UNION
                    SELECT
                        pm1.code as product_model_code, cp.category_id
                    FROM 
                        pim_catalog_product_model pm1
                        JOIN pim_catalog_product_model pm2 on pm2.id = pm1.parent_id
                        JOIN pim_catalog_category_product_model cp ON cp.product_model_id = pm2.id
                    WHERE pm1.code = :code
                ) as product_model_categories
                LEFT JOIN 
                 (
                    SELECT
                        pca.category_id,
                        pca.edit_items, 
                        pca.own_items
                    FROM pimee_security_product_category_access pca 
                    JOIN oro_access_group ag ON pca.user_group_id = ag.id
                    JOIN oro_user_access_group uag ON uag.group_id = ag.id AND uag.user_id = :user_id
                ) access ON access.category_id = product_model_categories.category_id
            GROUP BY 
                product_model_categories.product_model_code
SQL;

        $result = $this->connection->executeQuery(
            $sql,
            ['code' => $productModelCode, 'user_id' =>$userId]
        )->fetch();

        $userRightsOnProduct = new UserRightsOnProductModel(
            $productModelCode,
            $userId,
            (int) $result['count_editable_categories'],
            (int) $result['count_ownable_categories'],
            (int) $result['number_categories']
        );

        return $userRightsOnProduct;
    }
}
