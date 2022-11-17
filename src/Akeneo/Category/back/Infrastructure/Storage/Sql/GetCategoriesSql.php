<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\GetCategoriesInterface;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoriesSql implements GetCategoriesInterface
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function afterOffset(
        array $categoryCodes,
        int $limit,
        int $offset,
        bool $isEnrichedAttributes
    ): array
    {
        $condition['sqlWhere'] = $this->searchFilter($categoryCodes);
        $condition['limitOffset'] = $this->buildLimitOffset($limit, $offset);
        $condition['params'] = [
            'category_codes' => $categoryCodes,
            'with_enriched_attributes' => $isEnrichedAttributes ?: false
        ];
        $condition['types'] = [
            'category_codes' => Connection::PARAM_STR_ARRAY,
            'with_enriched_attributes' => \PDO::PARAM_BOOL
        ];

        return $this->execute($condition);
    }
    //TODO: Will be replaced by filtering service. https://akeneo.atlassian.net/browse/GRF-376
    public function searchFilter(array $searchParameter): string
    {
        if (empty($searchParameter)) {
            $sqlWhere = '1=1';
        } else {
            $sqlWhere = 'category.code IN (:category_codes)';
        }
        return $sqlWhere;
    }

    private function buildLimitOffset(int $limit, int $offset): string
    {
        $sqlLimitAndOffset = sprintf('LIMIT %d', $limit);
        if ($offset !== 0){
            $sqlLimitAndOffset .= sprintf(' OFFSET %d', $offset);
        }

        return $sqlLimitAndOffset;
    }

    /**
     * @param array $condition
     *
     * @return array<Category>
     * @throws \Doctrine\DBAL\Exception
     * @throws \JsonException
     */
    private function execute(array $condition): array
    {
        $sqlWhere = $condition['sqlWhere'];
        $sqlLimitOffset = $condition['limitOffset'];

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
            $condition['params'],
            $condition['types']
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
}
