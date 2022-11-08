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
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @param array<string> $categoryCodes
     *
     * @return array<Category>
     * @throws \Doctrine\DBAL\Exception
     * @throws \JsonException
     */
    public function byCodes(array $categoryCodes, bool $isEnrichedAttributes): array
    {
        $condition['sqlWhere'] = 'category.code IN (:category_codes)';
        $condition['sqlGroupBy'] = 'GROUP BY category.code';
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
        $sqlGroupBy = $condition['sqlGroupBy'] ?? '';

        $sqlQuery = <<<SQL
            WITH translation as (
                SELECT category.code, JSON_OBJECTAGG(translation.locale, translation.label) as translations
                FROM pim_catalog_category category
                JOIN pim_catalog_category_translation translation ON translation.foreign_key = category.id
                WHERE $sqlWhere
                $sqlGroupBy
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
            $sqlGroupBy
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
