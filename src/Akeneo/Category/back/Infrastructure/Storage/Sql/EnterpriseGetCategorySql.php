<?php

declare(strict_types=1);

namespace AkeneoEnterprise\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Domain\Model\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\PermissionCollection;
use Akeneo\Category\Domain\ValueObject\ValueCollection;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EnterpriseGetCategorySql implements GetCategoryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function byId(int $categoryId): ?Category
    {
        $condition['sqlWhere'] = 'category.id = :category_id';
        $condition['params'] = ['category_id' => $categoryId,];
        $condition['types'] = ['category_id' => \PDO::PARAM_INT,];

        return $this->execute($condition);
    }

    public function byCode(string $categoryCode): ?Category
    {
        $condition['sqlWhere'] = 'category.code = :category_code';
        $condition['params'] = ['category_code' => $categoryCode,];
        $condition['types'] = ['category_code' => \PDO::PARAM_STR,];

        return $this->execute($condition);
    }

    private function execute(array $condition): ?Category
    {
        $sqlWhere = $condition['sqlWhere'];

        $sqlQuery = <<<SQL
            WITH translation as (
                SELECT category.code, JSON_OBJECTAGG(category_translation.locale, category_translation.label) as translations
                FROM pim_catalog_category category
                JOIN pim_catalog_category_translation category_translation ON category_translation.foreign_key = category.id
                WHERE $sqlWhere
            ),
            permissions_view as (
                SELECT pca.category_id, JSON_ARRAYAGG(pca.user_group_id) as user_groups
                FROM pim_catalog_category category
                JOIN pimee_security_product_category_access pca ON pca.category_id = category.id
                WHERE pca.view_items = 1
                AND $sqlWhere
            ),
            permissions_edit as (
                SELECT pca.category_id, JSON_ARRAYAGG(pca.user_group_id) as user_groups
                FROM pim_catalog_category category
                JOIN pimee_security_product_category_access pca ON pca.category_id = category.id
                WHERE pca.edit_items = 1
                AND $sqlWhere
            ),
            permissions_own as (
                SELECT pca.category_id, JSON_ARRAYAGG(pca.user_group_id) as user_groups
                FROM pim_catalog_category category
                JOIN pimee_security_product_category_access pca ON pca.category_id = category.id
                WHERE pca.own_items = 1
                AND $sqlWhere
            )
            SELECT
                category.id, 
                category.code, 
                category.parent_id,
                category.root,
                translation.translations,
                category.value_collection,
                JSON_OBJECT(
                    'view', permissions_view.user_groups, 
                    'edit', permissions_edit.user_groups, 
                    'own', permissions_own.user_groups
                ) as permissions
            FROM pim_catalog_category category
            LEFT JOIN translation ON translation.code = category.code
            LEFT JOIN permissions_view ON permissions_view.category_id = category.id
            LEFT JOIN permissions_edit ON permissions_edit.category_id = category.id
            LEFT JOIN permissions_own ON permissions_own.category_id = category.id
            WHERE $sqlWhere
        SQL;

        $result = $this->connection->executeQuery(
            $sqlQuery,
            $condition['params'],
            $condition['types']
        )->fetchAssociative();

        if (!$result) {
            return null;
        }

        return new Category(
            new CategoryId((int)$result['id']),
            new Code($result['code']),
            $result['translations'] ?
                LabelCollection::fromArray(
                    json_decode(
                        $result['translations'],
                        true,
                        512,
                        JSON_THROW_ON_ERROR
                    )
                ) : null,
            $result['parent_id'] ? new CategoryId((int)$result['parent_id']) : null,
            $result['root'] ? new CategoryId((int)$result['root']) : null,
            $result['value_collection'] ?
                ValueCollection::fromArray(json_decode($result['value_collection'], true)) : null,
            $result['permissions'] ?
                PermissionCollection::fromArray(json_decode($result['permissions'], true)) : null
        );
    }
}
