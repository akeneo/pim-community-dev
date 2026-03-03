<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductUuidsNotSynchronisedBetweenEsAndMysql
{
    public function __construct(
        private readonly Client $productAndProductModelClient,
        private readonly Connection $connection,
    ) {
    }

    public function byBatchesOf(int $batchSize): iterable
    {
        $lastUuidAsBytes = '';
        $sql = <<< SQL
SELECT CONCAT('product_',BIN_TO_UUID(uuid)) AS _id, uuid, DATE_FORMAT(updated, '%Y-%m-%dT%TZ') AS updated
FROM pim_catalog_product
WHERE uuid > :lastUuid
ORDER BY uuid ASC
LIMIT :limit
SQL;
        while (true) {
            $rows = $this->connection->executeQuery(
                $sql,
                [
                    'lastUuid' => $lastUuidAsBytes,
                    'limit' => $batchSize,
                ],
                [
                    'lastUuid' => \PDO::PARAM_STR,
                    'limit' => \PDO::PARAM_INT,
                ]
            )->fetchAllAssociative();

            if (empty($rows)) {
                return;
            }

            $lastUuidAsBytes = end($rows)['uuid'];

            $existingMysqlIdentifiers = array_column($rows, '_id');

            $results = $this->productAndProductModelClient->search([
                'query' => [
                    'bool' => [
                        'must' => [
                            'ids' => [
                                'values' => $existingMysqlIdentifiers
                            ]
                        ],
                    ]
                ],
                '_source' => ['id', 'entity_updated'],
                'size' => $batchSize
            ]);

            $updatedById = [];
            foreach ($results['hits']['hits'] as $hit) {
                $updatedById[$hit['_source']['id']] = $hit['_source']['entity_updated'];
            }

            $diff = \array_map(
                static fn (array $row): UuidInterface => Uuid::fromBytes($row['uuid']),
                \array_filter(
                    $rows,
                    function (array $row) use ($updatedById): bool {
                        if (!isset($updatedById[$row['_id']])) {
                            // the product is not indexed at all
                            return true;
                        }
                        $updateDateInIndex = new \DateTimeImmutable($updatedById[$row['_id']]);
                        $updateDateInDb = new \DateTimeImmutable($row['updated']);

                        return $updateDateInDb != $updateDateInIndex;
                    }
                )
            );

            yield $diff;
        }
    }
}
