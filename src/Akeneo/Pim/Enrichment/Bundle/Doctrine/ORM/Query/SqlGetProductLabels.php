<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductLabelsInterface;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

class SqlGetProductLabels implements GetProductLabelsInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function byIdentifiersAndLocaleAndScope(array $identifiers, string $locale, string $channel): array
    {
        $query = <<<SQL
WITH main_identifier AS (
    SELECT id
    FROM pim_catalog_attribute
    WHERE main_identifier = 1
    LIMIT 1
)
SELECT
    pcpud.raw_data AS identifier,
    a.code as label_code, 
    a.is_localizable as label_is_localizable, 
    a.is_scopable AS label_is_scopable,
    JSON_MERGE_PATCH(COALESCE(pm1.raw_values, '{}'), COALESCE(pm.raw_values, '{}'), COALESCE(p.raw_values, '{}')) as raw_values
FROM pim_catalog_product p
LEFT JOIN pim_catalog_family f ON p.family_id = f.id
LEFT JOIN pim_catalog_attribute a ON f.label_attribute_id = a.id
LEFT JOIN pim_catalog_product_model pm on p.product_model_id = pm.id
LEFT JOIN pim_catalog_product_model pm1 ON pm.parent_id = pm1.id
LEFT JOIN pim_catalog_product_unique_data pcpud
    ON pcpud.product_uuid = p.uuid
    AND pcpud.attribute_id = (SELECT id FROM main_identifier)
WHERE pcpud.raw_data IN (:identifiers);
SQL;

        $results = $this->connection->executeQuery(
            $query,
            ['identifiers' => $identifiers],
            ['identifiers' => Connection::PARAM_STR_ARRAY]
        )->fetchAllAssociative();

        $labels = [];
        foreach ($results as $result) {
            $values = json_decode($result['raw_values'], true);

            $productIdentifier = $result['identifier'];
            $labelCode = $result['label_code'];
            $localeIndex = $result['label_is_localizable'] ? $locale : '<all_locales>';
            $scopeIndex = $result['label_is_scopable'] ? $channel : '<all_channels>';

            $labels[$productIdentifier] = $values[$labelCode][$scopeIndex][$localeIndex] ?? null;
        }

        return $labels;
    }

    public function byUuidsAndLocaleAndScope(array $uuids, string $locale, string $channel): array
    {
        $query = <<<SQL
SELECT
    BIN_TO_UUID(p.uuid) AS uuid,
    a.code as label_code, 
    a.is_localizable as label_is_localizable, 
    a.is_scopable AS label_is_scopable,
    JSON_MERGE_PATCH(COALESCE(pm1.raw_values, '{}'), COALESCE(pm.raw_values, '{}'), COALESCE(p.raw_values, '{}')) as raw_values
FROM pim_catalog_product p
LEFT JOIN pim_catalog_family f ON p.family_id = f.id
LEFT JOIN pim_catalog_attribute a ON f.label_attribute_id = a.id
LEFT JOIN pim_catalog_product_model pm on p.product_model_id = pm.id
LEFT JOIN pim_catalog_product_model pm1 ON pm.parent_id = pm1.id
WHERE p.uuid IN (:uuids);
SQL;

        $uuidAsBytes = \array_map(
            static fn (string $uuid): ?string => Uuid::isValid($uuid) ? Uuid::fromString($uuid)->getBytes() : null,
            $uuids
        );

        $results = $this->connection->executeQuery(
            $query,
            ['uuids' => \array_filter($uuidAsBytes)],
            ['uuids' => Connection::PARAM_STR_ARRAY]
        )->fetchAllAssociative();

        $labels = [];
        foreach ($results as $result) {
            $values = json_decode($result['raw_values'], true);

            $productUuid = $result['uuid'];
            $labelCode = $result['label_code'];
            $localeIndex = $result['label_is_localizable'] ? $locale : '<all_locales>';
            $scopeIndex = $result['label_is_scopable'] ? $channel : '<all_channels>';

            $labels[$productUuid] = $values[$labelCode][$scopeIndex][$localeIndex] ?? null;
        }

        return $labels;
    }
}
