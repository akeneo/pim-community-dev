<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\SaveProductCompletenesses;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DoubleStorageCompletenesses implements SaveProductCompletenesses
{
    private const TABLE_NAME = 'pim_catalog_product_completeness';

    public function __construct(
        private readonly SaveProductCompletenesses $legacySaveProductCompletenesses,
        private readonly SaveProductCompletenesses $saveProductCompletenesses,
        private readonly Connection $connection,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function save(ProductCompletenessWithMissingAttributeCodesCollection $completenesses): void
    {
        $this->saveAll([$completenesses]);
    }

    /**
     * @throws \Exception
     */
    public function saveAll(array $productCompletenessCollections): void
    {
        if ($this->newTableExists()) {
            $this->saveProductCompletenesses->saveAll($productCompletenessCollections);
        }

        $this->legacySaveProductCompletenesses->saveAll($productCompletenessCollections);
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
