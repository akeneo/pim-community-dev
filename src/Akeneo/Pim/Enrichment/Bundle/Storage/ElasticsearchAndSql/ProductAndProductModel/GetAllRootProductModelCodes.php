<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetAllRootProductModelCodes
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function byBatchesOf(int $batchSize): iterable
    {
        $formerId = 0;
        $sql = <<< SQL
SELECT id, code
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
                    'limit' => $batchSize,
                ],
                [
                    'formerId' => \PDO::PARAM_INT,
                    'limit' => \PDO::PARAM_INT,
                ]
            )->fetchAllAssociative();

            if (empty($rows)) {
                return;
            }

            $formerId = (int)end($rows)['id'];
            yield array_column($rows, 'code');
        }
    }
}
