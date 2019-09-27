<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql;

use Doctrine\DBAL\Connection;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetDescendantProductModelIds
{
    /** @var Connection  */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fromProductModelIds(array $productModelIds): array
    {
        if (empty($productModelIds)) {
            return [];
        }

        $sql = <<<SQL
SELECT DISTINCT
    sub_product_model.id
FROM
    pim_catalog_product_model product_model
    INNER JOIN pim_catalog_product_model sub_product_model ON product_model.id = sub_product_model.parent_id
WHERE product_model.id IN (:ids)
SQL;

        return $this->connection->executeQuery(
            $sql,
            ['ids' => $productModelIds],
            ['ids' => Connection::PARAM_STR_ARRAY]
        )->fetchAll(\PDO::FETCH_COLUMN, 0);
    }
}
