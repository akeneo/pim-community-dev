<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel;

use Doctrine\DBAL\Connection;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAncestorAndDescendantProductModelCodes
{
    /** @var Connection  */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fromProductModelCodes(array $productModelCodes): array
    {
        if (empty($productModelCodes)) {
            return [];
        }

        $sql = <<<SQL
WITH
filter_product_model AS (
    SELECT id, parent_id FROM pim_catalog_product_model WHERE code IN (:codes)
)
SELECT
    root_product_model.code
FROM
    filter_product_model
    INNER JOIN pim_catalog_product_model root_product_model ON filter_product_model.parent_id = root_product_model.id
UNION DISTINCT
SELECT
    sub_product_model.code
FROM
    filter_product_model
    INNER JOIN pim_catalog_product_model sub_product_model ON filter_product_model.id = sub_product_model.parent_id
SQL;

        return $this->connection->executeQuery(
            $sql,
            ['codes' => $productModelCodes],
            ['codes' => Connection::PARAM_STR_ARRAY]
        )->fetchAll(\PDO::FETCH_COLUMN, 0);
    }

    public function getOnlyAncestorsFromProductModelIds(array $productModelIds): array
    {
        $sql = <<<SQL
SELECT DISTINCT
    root_product_model.code
FROM
    pim_catalog_product_model product_model
    INNER JOIN pim_catalog_product_model root_product_model ON product_model.parent_id = root_product_model.id
WHERE product_model.id IN (:ids)
SQL;

        return $this->connection->executeQuery(
            $sql,
            ['ids' => $productModelIds],
            ['ids' => Connection::PARAM_STR_ARRAY]
        )->fetchAll(\PDO::FETCH_COLUMN, 0);
    }
}
