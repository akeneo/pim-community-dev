<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetAllProductUuids
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function byBatchesOf(int $batchSize): iterable
    {
        $lastUuidAsBytes = '';
        $sql = <<<SQL
SELECT uuid
FROM pim_catalog_product
WHERE uuid > :lastUuid
ORDER BY uuid ASC
LIMIT :limit
SQL;
        while (true) {
            $rows = $this->connection->fetchFirstColumn(
                $sql,
                [
                    'lastUuid' => $lastUuidAsBytes,
                    'limit' => $batchSize,
                ],
                [
                    'lastUuid' => \PDO::PARAM_STR,
                    'limit' => \PDO::PARAM_INT,
                ]
            );

            if (empty($rows)) {
                return;
            }

            $lastUuidAsBytes = end($rows);

            yield array_map(fn (string $uuid): UuidInterface => Uuid::fromBytes($uuid), $rows);
        }
    }
}
