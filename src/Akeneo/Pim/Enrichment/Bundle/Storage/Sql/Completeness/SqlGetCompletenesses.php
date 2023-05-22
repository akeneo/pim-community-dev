<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Completeness;

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
        private Connection $connection
    ) {
    }

    public function fromProductUuid(UuidInterface $productUuid): ProductCompletenessCollection
    {
        return $this->fromProductUuids([$productUuid])[$productUuid->toString()];
    }

    /**
     * {@inheritdoc}
     */
    public function fromProductUuids(array $productUuids, ?string $channel = null, array $locales = []): array
    {
        $uuidsAsBytes = \array_map(fn($productUuid) => $productUuid->getBytes(), \array_values($productUuids));

        $sql =
<<<SQL
SELECT BIN_TO_UUID(product_uuid) AS uuid, completeness 
FROM pim_catalog_product_completeness
WHERE product_uuid IN (:productUuids) 
SQL;
        $rows = $this->connection->executeQuery($sql,
            [
                'productUuids' => $uuidsAsBytes,
            ],
            [
                'productUuids' => Connection::PARAM_STR_ARRAY
            ]
        )->fetchAllAssociative();

        $results = [];
        foreach ($rows as $row) {
            $channels = json_decode($row['completeness'],true);
            $completenesses = [];
            foreach($channels as $completenessChannel => $completenessLocales) {
                if ($channel && $completenessChannel !== $channel) {
                    continue;
                }

                $filterByLocales = array_filter($completenessLocales, function ($locale) use ($locales) {
                    return empty($locales) || in_array($locale, $locales);
                }, ARRAY_FILTER_USE_KEY);

                foreach ($filterByLocales as $locale => $value) {
                    $completenesses[] = new ProductCompleteness($completenessChannel, $locale, $value['required'], $value['missing']);
                }
            }
            $results[$row['uuid']] = new ProductCompletenessCollection(Uuid::fromString($row['uuid']), $completenesses);
        }

        return $results;
    }
}
