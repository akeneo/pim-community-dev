<?php

namespace Akeneo\Category\Infrastructure\EventSubscriber\Cleaner\Sql;

use Doctrine\DBAL\Connection;

final class UpdateCategoryEnrichedValuesSql implements UpdateCategoryEnrichedValues
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /**
     * @param array<string, string> $enrichedValuesByCode
     *          ex. [
     *              'socks' => '{
     *                  "attribute_codes": [
     *                      "title|87939c45-1d85-4134-9579-d594fff65030",
     *                      "photo|8587cda6-58c8-47fa-9278-033e1d8c735c"
     *                  ],
     *                  "photo|8587cda6-58c8-47fa-9278-033e1d8c735c": {
     *                      "data": {
     *                          "size": 168107,
     *                          "extension": "jpg",
     *                      {...}
     *                  }
     *              }',
     *              'shoes' => '{...}',
     *          ]
     * @return void
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
                    WHERE category.code = ?'
            ),
        );

        $statement = $this->connection->prepare(<<<SQL
            $queries
        SQL);

        $queryIndex = 0;
        foreach ($enrichedValuesByCode as $code => $value) {
            $statement->bindValue(++$queryIndex, $code, \PDO::PARAM_STR);
            $statement->bindValue(++$queryIndex, (string)$value, \PDO::PARAM_STR);
        }

        $statement->executeQuery();
    }
}
