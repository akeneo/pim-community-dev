<?php

namespace Akeneo\Pim\Permission\Bundle\Persistence\Sql\Category;

use Akeneo\Pim\Permission\Bundle\Category\GetCategoryProductPermissions;
use Doctrine\DBAL\Connection;

class GetCategoryProductPermissionsSql implements GetCategoryProductPermissions
{
    public function __construct(
        private Connection $connection
    ) {
    }

    /**
     * @param int $categoryId
     * @return array<string, array<integer, string>>|array
     * ex.
     * [
     *      "view" => [ ["id" => 1, "label" => "IT Support"], ["id" => 3, "label" => "Redactor"] ],
     *      "edit" => [ ["id" => 1, "label" => "IT Support"], ["id" => 3, "label" => "Redactor"] ],
     *      "own" => [ ["id" => 1, "label" => "IT Support"], ["id" => 3, "label" => "Redactor"] ],
     * ]
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(int $categoryId): array
    {
        $sqlWhere="category.id=:category_id";

        $sqlQuery = <<<SQL
            WITH permissions_view as (
                SELECT pca.category_id, JSON_ARRAYAGG(JSON_OBJECT('id', pca.user_group_id, 'label', oag.name)) as user_groups
                FROM pim_catalog_category category
                JOIN pimee_security_product_category_access pca ON pca.category_id = category.id
                JOIN oro_access_group oag ON pca.user_group_id=oag.id
                WHERE pca.view_items = 1
                AND $sqlWhere
            ),
            permissions_edit as (
                SELECT pca.category_id, JSON_ARRAYAGG(JSON_OBJECT('id', user_group_id, 'label', name)) as user_groups
                FROM pim_catalog_category category
                JOIN pimee_security_product_category_access pca ON pca.category_id = category.id
                JOIN oro_access_group oag ON pca.user_group_id=oag.id
                WHERE pca.edit_items = 1
                AND $sqlWhere
            ),
            permissions_own as (
                SELECT pca.category_id, JSON_ARRAYAGG(JSON_OBJECT('id', user_group_id, 'label', name)) as user_groups
                FROM pim_catalog_category category
                JOIN pimee_security_product_category_access pca ON pca.category_id = category.id
                JOIN oro_access_group oag ON pca.user_group_id=oag.id
                WHERE pca.own_items = 1
                AND $sqlWhere
            )
            SELECT
                category.id,
                JSON_OBJECT(
                    'view', permissions_view.user_groups,
                    'edit', permissions_edit.user_groups,
                    'own', permissions_own.user_groups
                ) as permissions
            FROM pim_catalog_category category
            LEFT JOIN permissions_view ON permissions_view.category_id = category.id
            LEFT JOIN permissions_edit ON permissions_edit.category_id = category.id
            LEFT JOIN permissions_own ON permissions_own.category_id = category.id
            WHERE $sqlWhere
        SQL;

        $result = $this->connection->executeQuery(
            $sqlQuery,
            ["category_id" => $categoryId],
            ["category_id" => \PDO::PARAM_INT]
        )->fetchAssociative();

        if (!$result || !array_key_exists('permissions', $result)) {
            return [];
        }

        return json_decode($result['permissions'], true);
    }
}
