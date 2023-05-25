<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Completeness;

use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\ChannelExistsWithLocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetCompletenesses implements GetProductCompletenesses
{
    public function __construct(
        private readonly Connection $connection,
        private readonly ChannelExistsWithLocaleInterface $channelExistsWithLocale,
    ) {
    }

    public function fromProductUuid(UuidInterface $productUuid): ProductCompletenessCollection
    {
        return $this->fromProductUuids([$productUuid])[$productUuid->toString()];
    }

    public function fromProductUuids(array $productUuids, ?string $channel = null, array $locales = []): array
    {
        $uuidsAsBytes = \array_map(fn ($productUuid) => $productUuid->getBytes(), \array_values($productUuids));

        $sql =
            <<<SQL
SELECT BIN_TO_UUID(product_uuid) AS uuid, completeness 
FROM pim_catalog_product_completeness
WHERE product_uuid IN (:productUuids) 
SQL;
        $rows = $this->connection->executeQuery(
            $sql,
            [
                'productUuids' => $uuidsAsBytes,
            ],
            [
                'productUuids' => Connection::PARAM_STR_ARRAY
            ]
        )->fetchAllAssociative();

        $results = [];
        foreach ($rows as $row) {
            $results[$row['uuid']] = $this->filterCompleteness(Uuid::fromString($row['uuid']), \json_decode($row['completeness'], true), $channel, $locales);
        }

        // to fill missing uuids
        $productUuidsAsStrings = \array_map(fn (UuidInterface $uuid): string => $uuid->toString(), $productUuids);
        $missingUuids = \array_diff($productUuidsAsStrings, \array_keys($results));
        if (!empty($missingUuids)) {
            foreach ($missingUuids as $missingUuid) {
                $results[$missingUuid] = new ProductCompletenessCollection(Uuid::fromString($missingUuid), []);
            }
        }

        return $results;
    }

    private function filterCompleteness(UuidInterface $uuid, array $completeness, ?string $channel, array $locales): ProductCompletenessCollection
    {
        $completenesses = [];
        \ksort($completeness);

        foreach ($completeness as $channelCode => $completenessByLocale) {
            if (!$this->channelExistsWithLocale->doesChannelExist($channelCode)) {
                continue;
            }

            if (null !== $channel && $channelCode !== $channel) {
                continue;
            }

            if (!empty($locales)) {
                $completenessByLocale = \array_intersect_key($completenessByLocale, \array_flip($locales));
            }

            foreach ($completenessByLocale as $localeCode => $completeness) {
                if ($this->channelExistsWithLocale->isLocaleBoundToChannel($localeCode, $channelCode)) {
                    $completenesses[] = new ProductCompleteness($channelCode, $localeCode, (int) $completeness['required'], (int) $completeness['missing']);
                }
            }
        }
        return new ProductCompletenessCollection($uuid, $completenesses);
    }
}
