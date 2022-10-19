<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Domain\Model\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\ValueCollection;
use Doctrine\DBAL\Connection;

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
        $condition['sqlWhere'] = 'WHERE category.id = :category_id';
        $condition['params'] = ['category_id' => $categoryId];
        $condition['types'] = ['category_id' => \PDO::PARAM_INT];

        return $this->execute($condition);
    }

    public function byCode(string $categoryCode): ?Category
    {
        $condition['sqlWhere'] = 'WHERE category.code = :category_code';
        $condition['params'] = ['category_code' => $categoryCode];
        $condition['types'] = ['category_code' => \PDO::PARAM_STR];

        return $this->execute($condition);
    }

    public function getTrees(): array
    {
        $condition['sqlWhere'] = 'WHERE category.parent_id IS NULL AND category.root = category.id';
        $condition['sqlGroupBy'] = 'GROUP BY category.code';
        $orderBy['sortField'] = 'created';
        $orderBy['direction'] = 'DESC';

        return $this->executeAll($condition, $orderBy);
    }

    private function sqlQuery(array $condition, array $orderBy = []): string
    {
        $sqlWhere = $condition['sqlWhere'] ?? '';
        $sqlGroupBy = $condition['sqlGroupBy'] ?? '';
        $sqlOrderBy = !empty($orderBy) ? sprintf('ORDER BY category.%s %s', $orderBy['sortField'], $orderBy['direction']) : '';

        return <<<SQL
            WITH translation as (
                SELECT category.code, JSON_OBJECTAGG(translation.locale, translation.label) as translations
                FROM pim_catalog_category category
                JOIN pim_catalog_category_translation translation ON translation.foreign_key = category.id
                $sqlWhere
                $sqlGroupBy
            )
            SELECT
                category.id,
                category.code, 
                category.parent_id,
                category.root as root_id,
                translation.translations,
                category.value_collection
            FROM 
                pim_catalog_category category
                LEFT JOIN translation ON translation.code = category.code
            $sqlWhere
            $sqlOrderBy
        SQL;
    }

    private function execute($condition): ?Category
    {
        $params = $condition['params'] ?? [];
        $types = $condition['types'] ?? [];

        $result = $this->connection->executeQuery(
            $this->sqlQuery($condition),
            $params,
            $types
        )->fetchAssociative();

        if (!$result) {
            return null;
        }

        return $this->buildCategory($result);
    }

    private function executeAll($condition, $orderBy): ?array
    {
        $results = $this->connection->executeQuery(
            $this->sqlQuery($condition, $orderBy)
        )->fetchAllAssociative();

        if (!$results) {
            return null;
        }

        return array_map(function ($result) {
            return $this->buildCategory($result);
        }, $results);
    }

    private function buildCategory($result): Category
    {
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
            $result['root_id'] ? new CategoryId((int)$result['root_id']) : null,
            $result['value_collection'] ?
                ValueCollection::fromArray(json_decode($result['value_collection'], true)) : null,
            null
        );
    }
}
