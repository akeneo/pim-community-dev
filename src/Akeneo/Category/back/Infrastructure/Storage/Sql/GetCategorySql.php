<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use PHP_CodeSniffer\Generators\Generator;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategorySql implements GetCategoryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function byId(int $categoryId): ?Category
    {
        $condition['sqlWhere'] = 'category.id = :category_id';
        $condition['params'] = ['category_id' => $categoryId];
        $condition['types'] = ['category_id' => \PDO::PARAM_INT];

        return $this->execute($condition);
    }

    public function byCode(string $categoryCode): ?Category
    {
        $condition['sqlWhere'] = 'category.code = :category_code';
        $condition['params'] = ['category_code' => $categoryCode];
        $condition['types'] = ['category_code' => \PDO::PARAM_STR];

        return $this->execute($condition);
    }

    /**
     * @param array $categoryCodes
     * @return \Generator<Category>
     */
    public function byCodes(array $categoryCodes): \Generator
    {
        $condition['sqlWhere'] = 'category.code IN (:category_code)';
        $condition['params'] = ['category_code' => $categoryCodes];
        $condition['types'] = ['category_code' => \PDO::PARAM_STR];

        return $this->execute($condition, true);
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \JsonException
     * @throws Exception
     */
    private function execute(array $condition, $asGenerator = false): \Generator|Category|null
    {
        $sqlWhere = $condition['sqlWhere'];

        $sqlQuery = <<<SQL
            WITH translation as (
                SELECT category.code, JSON_OBJECTAGG(translation.locale, translation.label) as translations
                FROM pim_catalog_category category
                JOIN pim_catalog_category_translation translation ON translation.foreign_key = category.id
                WHERE $sqlWhere
            ),
            template as (
                SELECT category.code as category_code, BIN_TO_UUID(category_template_uuid) as template_uuid
                FROM pim_catalog_category_tree_template template_category
                JOIN pim_catalog_category category ON category.root = template_category.category_tree_id
                WHERE $sqlWhere
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
            WHERE $sqlWhere
        SQL;

        if ($asGenerator) {
            $results = $this->connection->executeQuery(
                $sqlQuery,
                $condition['params'],
                $condition['types'],
            )->iterateAssociative();

            foreach ($results as $result) {
                yield Category::fromDatabase($result);
            }
        }

        $result = $this->connection->executeQuery(
            $sqlQuery,
            $condition['params'],
            $condition['types'],
        )->fetchAssociative();

        if (!$result) {
            return null;
        }

        return Category::fromDatabase($result);
    }
}
