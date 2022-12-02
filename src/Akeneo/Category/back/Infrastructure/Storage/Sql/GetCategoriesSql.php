<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\GetCategoriesInterface;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Infrastructure\DTO\ExternalApiSqlParameters;
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
     * @return array<Category>
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
                GROUP BY category.code
            )
            SELECT
                category.id,
                category.code, 
                category.parent_id,
                category.root as root_id,
                category.updated,
                category.lft,
                category.rgt,
                category.lvl,
                translation.translations,
                IF(:with_enriched_attributes, category.value_collection, '') as value_collection
            FROM 
                pim_catalog_category category
                LEFT JOIN translation ON translation.code = category.code
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

        $retrievedCategories = [];
        foreach ($results as $rawCategory) {
            $retrievedCategories[] = Category::fromDatabase($rawCategory);
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
