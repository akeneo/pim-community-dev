<?php

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel;

use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChunkProductModelCodes
{
    public function __construct(private Connection $dbConnection)
    {
    }

    /**
     * Naïve implementation to chunk elements by groups that does not exceed a given size for the raw values.
     * The main purpose is to adapt the size of the batch dynamically to not reach the PHP memory limit.
     *
     * It is not optimized to balance the size between the groups, as the solution would be more complex.
     * @see https://en.wikipedia.org/wiki/Bin_packing_problem
     */
    public function byRawValuesSize(array $productModelCodes, int $maxSizeInBytesPerChunk)
    {
        // ORDER BY is not in dedicated query to avoid Out of sort memory exception
        // because raw_values are too big in the buffer, even if not useful for the ordering
        // DISTINCT is trick to force to materialize the CTE before ordering the results
        $query = <<<SQL
            WITH product_model_size as (
                SELECT /*+ SET_VAR( range_optimizer_max_mem_size = 50000000) */ DISTINCT 
                    product_model.code, 
                    (JSON_STORAGE_SIZE(product_model.raw_values) + COALESCE(JSON_STORAGE_SIZE(root.raw_values), 0)) as size 
                FROM pim_catalog_product_model product_model
                LEFT JOIN pim_catalog_product_model root on root.id = product_model.parent_id
                WHERE product_model.code IN (:codes)
            )
            SELECT code, size
            FROM product_model_size
            ORDER BY FIELD(code, :codes)
        SQL;

        $results = $this->dbConnection->executeQuery(
            $query,
            ['codes' => $productModelCodes,],
            ['codes' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        );

        $chunks = [];
        $chunk = [];
        $chunkSize = 0;

        foreach ($results as $row) {
            if ($chunkSize + (int) $row['size'] < $maxSizeInBytesPerChunk) {
                $chunk[] = $row['code'];
                $chunkSize += $row['size'];
            } else {
                $chunks[] = $chunk;
                $chunk = [$row['code']];
                $chunkSize = (int) $row['size'];
            }
        }

        $chunks[] = $chunk;

        return $chunks;
    }
}
