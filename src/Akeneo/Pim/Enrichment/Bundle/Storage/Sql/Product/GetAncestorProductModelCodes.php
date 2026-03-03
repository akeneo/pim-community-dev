<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product;

use Doctrine\DBAL\Connection;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAncestorProductModelCodes
{
    public function __construct(
        private Connection $connection
    ) {
    }

    /**
     * @param UuidInterface[] $productUuids
     *
     * @return string[]
     */
    public function fromProductUuids(array $productUuids): array
    {
        if (empty($productUuids)) {
            return [];
        }

        $sql = <<<SQL
WITH sub_product_model AS (
    SELECT parent.parent_id, parent.code
    FROM pim_catalog_product product
    INNER JOIN pim_catalog_product_model parent ON parent.id = product.product_model_id
    WHERE product.uuid IN (:uuids)
)
SELECT sub_product_model.code
FROM sub_product_model
UNION
SELECT root.code
FROM sub_product_model
INNER JOIN pim_catalog_product_model root ON root.id = sub_product_model.parent_id;
SQL;

        $productUuidsAsBytes = \array_map(static fn (UuidInterface $uuid): string => $uuid->getBytes(), $productUuids);

        return $this->connection->executeQuery(
            $sql,
            ['uuids' => $productUuidsAsBytes],
            ['uuids' => Connection::PARAM_STR_ARRAY]
        )->fetchFirstColumn();
    }
}
