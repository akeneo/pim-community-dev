<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\ExternalApiSqlParameters;
use Akeneo\Category\Application\Query\GetCategoriesInterface;
use Akeneo\Category\ServiceApi\ExternalApiCategory;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetCategoriesSql implements GetCategoriesInterface
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @return array<ExternalApiCategory>
     *
     * @throws \Doctrine\DBAL\Exception
     * @throws \JsonException|\Doctrine\DBAL\Driver\Exception
     */
    public function execute(ExternalApiSqlParameters $sqlParameters): array
    {
        $sqlWhere = $sqlParameters->getSqlWhere();
        $sqlLimitOffset = $sqlParameters->getLimitAndOffset();

        $sqlQuery = <<<SQL
            WITH category_filtered_cte as (
                SELECT category.id,
                    category.root,
                    category.lft,
                    category.parent_id,
                    category.value_collection
                FROM pim_catalog_category category
                WHERE $sqlWhere
                ORDER BY category.root,
                    category.lft
                $sqlLimitOffset
            ),
            translation_cte as (
                SELECT category.id as category_id,
                    JSON_OBJECTAGG(translation.locale, translation.label) as translations
                FROM category_filtered_cte category
                    INNER JOIN pim_catalog_category_translation translation ON translation.foreign_key = category.id
                WHERE translation.label IS NOT NULL
                    AND translation.label != ''
                GROUP BY category.id
            ),
            position_cte as (
                SELECT category_id,
                    position
                FROM (
                        SELECT category.id as category_id,
                            sibling.id as sibling_id,
                            ROW_NUMBER() over (
                                PARTITION BY category.parent_id,
                                category.id
                                ORDER BY sibling.lft
                            ) as position
                        FROM category_filtered_cte category
                            INNER JOIN pim_catalog_category sibling on category.parent_id = sibling.parent_id
                    ) r
                WHERE sibling_id = category_id
            ),
            value_collection_cte as (
                SELECT category.id as category_id,
                    category.value_collection
                FROM category_filtered_cte category
                    LEFT JOIN pim_catalog_category_tree_template ctt ON ctt.category_tree_id = category.id
                    LEFT JOIN pim_catalog_category_template template ON template.uuid = ctt.category_template_uuid
                WHERE template.is_deactivated IS NULL
                    OR template.is_deactivated = 0
            )
            SELECT category_filtered_cte.id,
                category.code,
                category.parent_id,
                parent_category.code as parent_code,
                category.root as root_id,
                category.updated,
                category.lft,
                category.rgt,
                category.lvl,
                translation_cte.translations,
                IF(
                    :with_position,
                    COALESCE(position_cte.position, 1),
                    ''
                ) as position,
                IF(
                    :with_enriched_attributes,
                    value_collection_cte.value_collection,
                    ''
                ) as value_collection
            FROM category_filtered_cte
                LEFT JOIN pim_catalog_category as category on category_filtered_cte.id = category.id
                LEFT JOIN pim_catalog_category as parent_category on category.parent_id = parent_category.id
                LEFT JOIN translation_cte ON category_filtered_cte.id = translation_cte.category_id
                LEFT JOIN position_cte on category_filtered_cte.id = position_cte.category_id
                LEFT JOIN value_collection_cte on category_filtered_cte.id = value_collection_cte.category_id;
        SQL;

        $results = $this->connection->executeQuery(
            $sqlQuery,
            $sqlParameters->getParams(),
            $sqlParameters->getTypes(),
        )->fetchAllAssociative();

        if (!$results) {
            return [];
        }

        $retrievedCategories = [];
        foreach ($results as $rawCategory) {
            $retrievedCategories[] = ExternalApiCategory::fromDatabase($rawCategory);
        }

        return $retrievedCategories;
    }

    public function count(ExternalApiSqlParameters $parameters): int|null
    {
        $sqlWhere = $parameters->getSqlWhere();

        $sqlQuery = <<<SQL
            SELECT COUNT(category.id)
            FROM pim_catalog_category category
            WHERE $sqlWhere
        SQL;

        $result = $this->connection->executeQuery(
            $sqlQuery,
            $parameters->getParams(),
            $parameters->getTypes(),
        )->fetchOne();

        return $result ? (int) $result : null;
    }
}
