<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel;

use Doctrine\DBAL\Connection;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetDescendantVariantProductIdentifiers
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
    SELECT id, parent_id, code FROM pim_catalog_product_model WHERE code IN (:codes)
)
SELECT
    product.identifier
FROM filter_product_model
    INNER JOIN pim_catalog_product product ON filter_product_model.id = product.product_model_id
UNION DISTINCT
SELECT
    product.identifier
FROM filter_product_model
    INNER JOIN pim_catalog_product_model product_model ON filter_product_model.id = product_model.parent_id
    INNER JOIN pim_catalog_product product             ON product_model.id = product.product_model_id
SQL;

        return $this->connection->executeQuery(
            $sql,
            ['codes' => $productModelCodes],
            ['codes' => Connection::PARAM_STR_ARRAY]
        )->fetchAll(\PDO::FETCH_COLUMN, 0);
    }
}
