<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetProductCompletenessRatio;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SwitchSqlGetCompletenessRatio implements GetProductCompletenessRatio
{
    private const TABLE_NAME = 'pim_catalog_product_completeness';

    public function __construct(
        private readonly GetProductCompletenessRatio $legacyGetProductCompletenessRatio,
        private readonly GetProductCompletenessRatio $getProductCompletenessRatio,
        private readonly Connection $connection,
    ) {
    }

    public function forChannelCodeAndLocaleCode(UuidInterface $productUuid, string $channelCode, string $localeCode): ?int
    {
        if ($this->newTableExists()) {
            return $this->getProductCompletenessRatio->forChannelCodeAndLocaleCode($productUuid, $channelCode, $localeCode);
        }
        return $this->legacyGetProductCompletenessRatio->forChannelCodeAndLocaleCode($productUuid, $channelCode, $localeCode);
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
