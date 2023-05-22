<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\ProductCompletenessRemoverInterface;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductCompletenessRemover implements ProductCompletenessRemoverInterface
{
    public function __construct(
        private Connection $connection
    ) {
    }

    /**
     * see deleteProducts() below
     */
    public function deleteForOneProduct(UuidInterface $productUuid): int
    {
        return $this->deleteForProducts([$productUuid]);
    }

    /**
     * delete the elements from the completeness table
     * related to products passed as arguments
     * It returns the count of elements deleted.
     */
    public function deleteForProducts(array $productUuids): int
    {
        if ([] === $productUuids) {
            return 0;
        }

        $sql = <<<SQL
DELETE FROM pim_catalog_product_completeness AS pcc
WHERE pcc.product_uuid in (?)
SQL;
        $stmt = $this->connection->executeQuery(
            $sql,
            [\array_map(
                fn (UuidInterface $uuid): string => $uuid->getBytes(),
                $productUuids
            )],
            [Connection::PARAM_STR_ARRAY]
        );

        if (!method_exists($stmt, 'rowCount')) {
            throw new \RuntimeException('Cannot find the count of rows.');
        }

        return $stmt->rowCount();
    }
}
