<?php

namespace Akeneo\Category\Infrastructure\Storage\Sql\Update;

use Akeneo\Category\Application\Storage\UpdateCategoryEnrichedValues;
use Akeneo\Category\Domain\ValueObject\ValueCollection;
use Doctrine\DBAL\Connection;

final class UpdateCategoryEnrichedValuesSql implements UpdateCategoryEnrichedValues
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /**
     * ex of value for $enrichedValuesByCode.
     *  [
     *    'socks' => '{
     *         "photo|8587cda6-58c8-47fa-9278-033e1d8c735c": {
     *              "data": {
     *                   "size": 168107,
     *                   "extension": "jpg",
     *                   {...}
     *              }
     *         }',
     *         {...},
     *    },
     *    'shoes' => '{...}',
     * ].
     *
     * @param array<string, ValueCollection> $enrichedValuesByCode
     *
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function execute(array $enrichedValuesByCode): void
    {
        $queries = \implode(
            ';',
            \array_fill(
                0,
                \count($enrichedValuesByCode),
                'UPDATE pim_catalog_category as category
                    SET category.value_collection=? 
                    WHERE category.code = ?',
            ),
        );

        $statement = $this->connection->prepare(<<<SQL
            $queries
        SQL);

        $queryIndex = 0;
        foreach ($enrichedValuesByCode as $code => $value) {
            $statement->bindValue(++$queryIndex, json_encode($value->normalize(), JSON_THROW_ON_ERROR), \PDO::PARAM_STR);
            $statement->bindValue(++$queryIndex, $code, \PDO::PARAM_STR);
        }

        $statement->executeQuery();
    }
}
