<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductModelLabelsInterface;
use Doctrine\DBAL\Connection;

class SqlGetProductModelLabels implements GetProductModelLabelsInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function byCodesAndLocaleAndScope(array $codes, string $locale, string $scope): array
    {
        $query = <<<SQL
SELECT
    pm.code,
    a.code as label_code, 
    a.is_localizable as label_is_localizable, 
    a.is_scopable AS label_is_scopable,
    JSON_MERGE_PATCH(COALESCE(pm1.raw_values, '{}'), COALESCE(pm.raw_values, '{}')) as raw_values
FROM pim_catalog_product_model pm
LEFT JOIN pim_catalog_product_model pm1 ON pm.parent_id = pm1.id
JOIN pim_catalog_family_variant fv ON pm.family_variant_id = fv.id
JOIN pim_catalog_family f ON f.id = fv.family_id
JOIN pim_catalog_attribute a ON f.label_attribute_id = a.id
WHERE pm.code IN (:codes);
SQL;

        $results = $this->connection->executeQuery(
            $query,
            ['codes' => $codes],
            ['codes' => Connection::PARAM_STR_ARRAY]
        )->fetchAllAssociative();

        $labels = [];
        foreach ($results as $result) {
            $values = json_decode($result['raw_values'], true);

            $productModelCode = $result['code'];
            $labelCode = $result['label_code'];
            $localeIndex = $result['label_is_localizable'] ? $locale : '<all_locales>';
            $scopeIndex = $result['label_is_scopable'] ? $scope : '<all_channels>';

            $labels[$productModelCode] = $values[$labelCode][$scopeIndex][$localeIndex] ?? null;
        }

        return $labels;
    }
}
