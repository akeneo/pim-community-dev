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
final class SwitchCompletenessRemover implements ProductCompletenessRemoverInterface
{
    private const TABLE_NAME = 'pim_catalog_product_completeness';
    public function __construct(
        private ProductCompletenessRemoverInterface $completenessRemover,
        private ProductCompletenessRemoverInterface $productCompletenessRemover,
        private Connection $connection,
    ) {
    }

    public function deleteForOneProduct(UuidInterface $productUuid): int
    {
        return $this->deleteForProducts([$productUuid]);
    }

    public function deleteForProducts(array $productUuids): int
    {
        if ($this->newTableExists()) {
            return $this->productCompletenessRemover->deleteForProducts($productUuids);
        }
        return $this->completenessRemover->deleteForProducts($productUuids);
    }

    private function newTableExists(): bool
    {
        return $this->connection->executeQuery(
            'SHOW TABLES LIKE :tableName',
            [
                'tableName' => self::TABLE_NAME,
            ]
        )->rowCount() >= 1;
    }
}
