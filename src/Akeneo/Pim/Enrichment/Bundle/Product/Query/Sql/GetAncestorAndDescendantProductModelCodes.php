<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql;

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
SELECT
    root_product_model.code
FROM
    pim_catalog_product_model product_model
    INNER JOIN pim_catalog_product_model root_product_model ON product_model.parent_id = root_product_model.id
WHERE product_model.code IN (:codes)
SQL;

        return $this->connection->executeQuery(
            $sql,
            ['codes' => $productModelCodes],
            ['codes' => Connection::PARAM_STR_ARRAY]
        )->fetchAll(\PDO::FETCH_COLUMN, 0);
    }
}
