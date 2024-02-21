<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryByIds;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetCategoryByIdsSql implements GetCategoryByIds
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function __invoke(array $categoryIds): array
    {
        $sqlQuery = <<<SQL
            WITH translation as (
                SELECT category.code, JSON_OBJECTAGG(translation.locale, translation.label) as translations
                FROM pim_catalog_category category
                JOIN pim_catalog_category_translation translation ON translation.foreign_key = category.id
                WHERE category.id IN (:ids)
                GROUP BY category.id
            ),
            template as (
                SELECT category.code as category_code, BIN_TO_UUID(category_template_uuid) as template_uuid
                FROM pim_catalog_category_tree_template template_category
                JOIN pim_catalog_category category ON category.root = template_category.category_tree_id
                WHERE category.id IN (:ids)
            )
            SELECT
                category.id,
                category.code,
                category.parent_id,
                category.root as root_id,
                category.lft,
                category.rgt,
                category.lvl,
                category.updated,
                translation.translations,
                category.value_collection,
                template.template_uuid
            FROM 
                pim_catalog_category category
                LEFT JOIN translation ON translation.code = category.code
                LEFT JOIN template ON category.code = template.category_code
            WHERE category.id IN (:ids)
        SQL;

        $rows = $this->connection->executeQuery(
            $sqlQuery,
            ['ids' => $categoryIds],
            ['ids' => Connection::PARAM_INT_ARRAY],
        )->fetchAllAssociative();

        if (!$rows) {
            return [];
        }

        return array_map(fn ($category) => Category::fromDatabase($category), $rows);
    }
}
