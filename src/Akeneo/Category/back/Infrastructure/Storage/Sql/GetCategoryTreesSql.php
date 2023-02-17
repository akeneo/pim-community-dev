<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Domain\Model\Classification\CategoryTree;
use Akeneo\Category\Domain\Query\GetCategoryTreesInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoryTreesSql implements GetCategoryTreesInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function getAll(): ?array
    {
        return $this->execute();
    }

    public function byIds(array $categryTreeIds): ?array
    {
        $condition['sqlAnd'] = 'AND category.id IN (:ids)';
        $condition['params'] = ['ids' => $categryTreeIds];
        $condition['types'] = ['ids' => Connection::PARAM_INT_ARRAY];

        return $this->execute($condition);
    }

    private function execute(array $condition = []): ?array
    {
        $sqlAnd = $condition['sqlAnd'] ?? '';
        $sqlParams = $condition['params'] ?? [];
        $sqlTypes = $condition['types'] ?? [];

        $sqlQuery = <<<SQL
            WITH category_tree_translation as (
                SELECT category.code, JSON_OBJECTAGG(translation.locale, translation.label) as translations
                FROM pim_catalog_category category
                JOIN pim_catalog_category_translation translation ON translation.foreign_key = category.id
                WHERE category.parent_id IS NULL 
                AND category.root = category.id
                $sqlAnd
                GROUP BY category.code
            )
            SELECT
                category.id,
                category.code,
                category_tree_translation.translations,
                BIN_TO_UUID(template.uuid) as template_uuid,
                template.code as template_code,
                template.labels as template_labels
            FROM 
                pim_catalog_category category
                LEFT JOIN category_tree_translation ON category_tree_translation.code = category.code
                LEFT JOIN pim_catalog_category_tree_template ctt ON ctt.category_tree_id = category.id
                LEFT JOIN pim_catalog_category_template template ON template.uuid = ctt.category_template_uuid
            WHERE category.parent_id IS NULL 
            AND category.root = category.id
            $sqlAnd
            ORDER BY category.created DESC
        SQL;

        $results = $this->connection->executeQuery(
            $sqlQuery,
            $sqlParams,
            $sqlTypes,
        )->fetchAllAssociative();

        if (empty($results)) {
            return null;
        }

        return array_map(static function ($result) {
            return CategoryTree::fromDatabase($result);
        }, $results);
    }
}
