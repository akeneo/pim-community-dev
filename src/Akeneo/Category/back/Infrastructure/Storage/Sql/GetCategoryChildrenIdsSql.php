<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\GetCategoryChildrenIds;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetCategoryChildrenIdsSql implements GetCategoryChildrenIds
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @return array<int>
     *
     * @throws \Doctrine\DBAL\Exception
     * @throws \JsonException|\Doctrine\DBAL\Driver\Exception
     */
    public function __invoke(int $categoryId): array
    {
        $sqlQuery = <<<SQL
            WITH RECURSIVE child_categories as (
                SELECT id, code, parent_id
                FROM pim_catalog_category AS children
                WHERE children.id = :category_id
                UNION
                SELECT pim_catalog_category.id, pim_catalog_category.code, pim_catalog_category.parent_id
                FROM pim_catalog_category
                INNER JOIN child_categories parent ON parent.id = pim_catalog_category.parent_id
            )
            SELECT id
            FROM child_categories
            WHERE id != :category_id;
        SQL;

        $results = $this->connection->executeQuery(
            $sqlQuery,
            [
                'category_id' => $categoryId,
            ],
            [
                'category_id' => \PDO::PARAM_INT,
            ],
        )->fetchAllAssociative();

        if (!$results) {
            return [];
        }

        return array_map(fn ($result) => (int) $result['id'], $results);
    }
}
