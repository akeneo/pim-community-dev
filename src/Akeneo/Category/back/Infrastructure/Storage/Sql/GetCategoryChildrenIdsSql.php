<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\ExternalApiSqlParameters;
use Akeneo\Category\Application\Query\GetCategoriesInterface;
use Akeneo\Category\Application\Query\GetCategoryChildrenIdsInterface;
use Akeneo\Category\ServiceApi\ExternalApiCategory;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetCategoryChildrenIdsSql implements GetCategoryChildrenIdsInterface
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
    public function execute(int $categoryId): array
    {
        $sqlQuery = <<<SQL
            SELECT
                category.id
            FROM 
                pim_catalog_category category
            WHERE category.root = :category_id
            AND category.parent_id IS NOT NULL 
        SQL;

        $results = $this->connection->executeQuery(
            $sqlQuery,
            [
                'category_id' => $categoryId
            ],
            [
                'category_id' => \PDO::PARAM_INT
            ]
        )->fetchAllAssociative();

        if (!$results) {
            return [];
        }

        return array_map(fn ($result) => (int) $result['id'], $results);
    }
}
