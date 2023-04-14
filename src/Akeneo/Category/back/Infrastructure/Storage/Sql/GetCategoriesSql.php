<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Enrichment\DeactivatedTemplateAttributesInValueCollectionCleaner;
use Akeneo\Category\Application\Query\ExternalApiSqlParameters;
use Akeneo\Category\Application\Query\GetCategoriesInterface;
use Akeneo\Category\Domain\Query\GetDeactivatedTemplateAttributes;
use Akeneo\Category\ServiceApi\ExternalApiCategory;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetCategoriesSql implements GetCategoriesInterface
{
    public function __construct(
        private readonly Connection $connection,
        private readonly GetDeactivatedTemplateAttributes $getDeactivatedTemplateAttributes,
        private readonly DeactivatedTemplateAttributesInValueCollectionCleaner $deactivatedAttributesInValueCollectionCleaner,
    ) {
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
            WITH translation as (
                SELECT category.code, JSON_OBJECTAGG(translation.locale, translation.label) as translations
                FROM pim_catalog_category category
                JOIN pim_catalog_category_translation translation ON translation.foreign_key = category.id
                WHERE $sqlWhere
                AND translation.label IS NOT NULL
                AND translation.label != ''
                GROUP BY category.code
            ),
            enriched_values as (
                SELECT category.id, category.value_collection
                FROM pim_catalog_category category
                LEFT JOIN pim_catalog_category_tree_template ctt ON ctt.category_tree_id = category.id
                LEFT JOIN pim_catalog_category_template template ON template.uuid = ctt.category_template_uuid
                WHERE $sqlWhere
                AND template.is_deactivated IS NULL
                    OR template.is_deactivated = 0
            ),
            position as (
                SELECT code, position
                FROM (
                    SELECT
                        category.code,
                        category.id as category_id,
                        sibling.id as sibling_id,
                        ROW_NUMBER() over (PARTITION BY category.parent_id ORDER BY sibling.lft) as position
                    FROM pim_catalog_category category
                        JOIN pim_catalog_category sibling on category.id = sibling.id
                    WHERE $sqlWhere
                    AND category.parent_id IS NOT NULL
                ) r
                WHERE sibling_id = category_id
            )
            SELECT
                category.id,
                category.code,
                category.parent_id,
                parent_category.code as parent_code,
                category.root as root_id,
                category.updated,
                category.lft,
                category.rgt,
                category.lvl,
                translation.translations,
                IF(:with_position, COALESCE(position.position, 1), '') as position,
                IF(:with_enriched_attributes, enriched_values.value_collection, '') as value_collection
            FROM
                pim_catalog_category category
                LEFT JOIN translation ON translation.code = category.code
                LEFT JOIN position on position.code = category.code
                LEFT JOIN enriched_values on enriched_values.id = category.id
                LEFT JOIN pim_catalog_category as parent_category on category.parent_id = parent_category.id
            WHERE $sqlWhere
            ORDER BY category.root, category.lft
            $sqlLimitOffset
        SQL;

        $results = $this->connection->executeQuery(
            $sqlQuery,
            $sqlParameters->getParams(),
            $sqlParameters->getTypes(),
        )->fetchAllAssociative();

        if (!$results) {
            return [];
        }
        $deactivatedAttributes = $this->getDeactivatedTemplateAttributes->execute();
        $retrievedCategories = [];

        foreach ($results as $rawCategory) {
            $filteredRawCategory = ($this->deactivatedAttributesInValueCollectionCleaner)($deactivatedAttributes, $rawCategory);
            $retrievedCategories[] = ExternalApiCategory::fromDatabase($filteredRawCategory);
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
