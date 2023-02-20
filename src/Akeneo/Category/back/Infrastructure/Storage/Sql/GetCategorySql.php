<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

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

        return $this->executeOne($condition);
    }

    public function byCode(string $categoryCode): ?Category
    {
        $condition['sqlWhere'] = 'category.code = :category_code';
        $condition['params'] = ['category_code' => $categoryCode];
        $condition['types'] = ['category_code' => \PDO::PARAM_STR];

        return $this->executeOne($condition);
    }

    /**
     * @param array<string> $categoryCodes
     *
     * @return \Generator<Category>
     */
    public function byCodes(array $categoryCodes): \Generator
    {
        $condition['sqlWhere'] = 'category.code IN (:category_codes)';
        $condition['params'] = ['category_codes' => $categoryCodes];
        $condition['types'] = ['category_codes' => Connection::PARAM_STR_ARRAY];

        return $this->executeAll($condition);
    }

    /**
     * @param array<int> $categoryIds
     *
     * @return \Generator<Category>
     */
    public function byIds(array $categoryIds): \Generator
    {
        $condition['sqlWhere'] = 'category.id IN (:category_ids)';
        $condition['params'] = ['category_ids' => $categoryIds];
        $condition['types'] = ['category_ids' => Connection::PARAM_INT_ARRAY];

        return $this->executeAll($condition);
    }

    /**
     * @param array<string, array<string, mixed>> $condition
     */
    private function getSQL(array $condition): string
    {
        $sqlWhere = $condition['sqlWhere'];

        return <<<SQL
            WITH translation as (
                SELECT category.code, JSON_OBJECTAGG(translation.locale, translation.label) as translations
                FROM pim_catalog_category category
                JOIN pim_catalog_category_translation translation ON translation.foreign_key = category.id
                WHERE $sqlWhere
                GROUP BY category.code
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
    }

    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \JsonException
     */
    private function executeOne(array $condition): ?Category
    {
        $result = $this->connection->executeQuery(
            $this->getSQL($condition),
            $condition['params'],
            $condition['types'],
        )->fetchAssociative();

        if (!$result) {
            return null;
        }

        return Category::fromDatabase($result);
    }

    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \JsonException
     */
    private function executeAll(array $condition): \Generator
    {
        $stmt = $this->connection->executeQuery(
            $this->getSQL($condition),
            $condition['params'],
            $condition['types'],
        );

        while (($result = $stmt->fetchAssociative()) !== false) {
            yield Category::fromDatabase($result);
        }
    }
}
