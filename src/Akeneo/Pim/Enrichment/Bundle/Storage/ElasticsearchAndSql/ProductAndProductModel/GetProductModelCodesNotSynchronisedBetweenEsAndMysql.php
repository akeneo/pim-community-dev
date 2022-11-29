<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductModelCodesNotSynchronisedBetweenEsAndMysql
{
    public function __construct(
        private readonly Client $productAndProductModelClient,
        private readonly Connection $connection,
    ) {
    }

    public function byBatchesOf(int $batchSize): iterable
    {
        $formerId = 0;
        $sql = <<< SQL
SELECT CONCAT('product_model_', id) AS _id, id, code, DATE_FORMAT(updated, '%Y-%m-%dT%TZ') AS updated
FROM pim_catalog_product_model
WHERE id > :formerId
AND parent_id IS NULL
ORDER BY id ASC
LIMIT :limit
SQL;
        while (true) {
            $rows = $this->connection->executeQuery(
                $sql,
                [
                    'formerId' => $formerId,
                    'limit' => $batchSize
                ],
                [
                    'formerId' => \PDO::PARAM_INT,
                    'limit' => \PDO::PARAM_INT
                ]
            )->fetchAllAssociative();

            if (empty($rows)) {
                return;
            }

            $formerId = (int)end($rows)['id'];
            $existingMysqlIdentifiers = array_column($rows, '_id');

            $results = $this->productAndProductModelClient->search([
                'query' => [
                    'bool' => [
                        'must' => [
                            'ids' => [
                                'values' => $existingMysqlIdentifiers
                            ]
                        ],
                    ],
                ],
                '_source' => ['id', 'entity_updated'],
                'size' => $batchSize
            ]);

            $updatedById = [];
            foreach ($results['hits']['hits'] as $hit) {
                $updatedById[$hit['_source']['id']] = $hit['_source']['entity_updated'];
            }

            $diff = \array_map(
                static fn (array $row): string => $row['code'],
                \array_filter(
                    $rows,
                    function (array $row) use ($updatedById): bool {
                        if (!isset($updatedById[$row['_id']])) {
                            // the model is not indexed at all
                            return true;
                        }
                        // if the PM is indexed, compare the update date in the index and in the DB
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
