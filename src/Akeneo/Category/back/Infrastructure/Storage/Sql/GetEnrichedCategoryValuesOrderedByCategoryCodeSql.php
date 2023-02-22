<?php

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\GetEnrichedCategoryValuesOrderedByCategoryCode;
use Doctrine\DBAL\Connection;

final class GetEnrichedCategoryValuesOrderedByCategoryCodeSql implements GetEnrichedCategoryValuesOrderedByCategoryCode
{
    public function __construct(private readonly Connection $dbalConnection)
    {
    }

    /**
     * @return array<string, string>
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function byLimitAndOffset(int $limit, int $offset): array
    {
        $query = <<<SQL
            SELECT code, value_collection
            FROM pim_catalog_category category
            WHERE category.value_collection IS NOT NULL
            LIMIT :limit OFFSET :offset
        SQL;

        $data = $this->dbalConnection->fetchAllAssociative(
            $query,
            [
                'limit' => $limit,
                'offset' => $offset,
            ],
            [
                'limit' => \PDO::PARAM_INT,
                'offset' => \PDO::PARAM_INT,
            ],
        );

        $results = [];
        foreach ($data as $key => $value) {
            $results[$value['code']] = $value['value_collection'];
        }

        return $results;
    }
}
