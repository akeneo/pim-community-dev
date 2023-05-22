<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DoubleSqlGetCompleteness implements GetProductCompletenesses
{
    private const TABLE_NAME = 'pim_catalog_product_completeness';

    public function __construct(
        private readonly GetProductCompletenesses $legacyGetProductCompletenesses,
        private readonly GetProductCompletenesses $getProductCompletenesses,
        private readonly Connection $connection,
    ) {
    }

    public function fromProductUuid(UuidInterface $productUuid): ProductCompletenessCollection
    {
        return $this->fromProductUuids([$productUuid])[$productUuid->toString()];
    }

    public function fromProductUuids(array $productUuids, ?string $channel = null, array $locales = []): array
    {
        // TODO check if table present AND data is also filled
        if ($this->newTableExists()) {
            return $this->getProductCompletenesses->fromProductUuids($productUuids, $channel, $locales);
        }
        return $this->legacyGetProductCompletenesses->fromProductUuids($productUuids, $channel, $locales);
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
