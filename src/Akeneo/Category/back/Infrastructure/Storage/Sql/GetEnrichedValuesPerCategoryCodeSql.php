<?php

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\GetEnrichedValuesPerCategoryCode;
use Akeneo\Category\Domain\ValueObject\ValueCollection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final class GetEnrichedValuesPerCategoryCodeSql implements GetEnrichedValuesPerCategoryCode
{
    public function __construct(private readonly Connection $dbalConnection)
    {
    }

    /**
     * @throws Exception
     * @throws \JsonException
     */
    public function byBatchesOf(int $batchSize): \Generator
    {
        $offset = 0;
        while (true) {
            $query = <<<SQL
            SELECT code, value_collection
            FROM pim_catalog_category category
            WHERE category.value_collection IS NOT NULL
            LIMIT :limit OFFSET :offset
        SQL;

            $data = $this->dbalConnection->fetchAllAssociative(
                $query,
                [
                    'limit' => $batchSize,
                    'offset' => $offset,
                ],
                [
                    'limit' => \PDO::PARAM_INT,
                    'offset' => \PDO::PARAM_INT,
                ],
            );
            if (empty($data)) {
                return;
            }
            $results = [];
            foreach ($data as $datum) {
                $results[$datum['code']] = ValueCollection::fromDatabase(
                    json_decode(
                        $datum['value_collection'],
                        true,
                        512,
                        JSON_THROW_ON_ERROR,
                    ),
                );
            }
            $offset += $batchSize;
            yield $results;
        }
    }
}
