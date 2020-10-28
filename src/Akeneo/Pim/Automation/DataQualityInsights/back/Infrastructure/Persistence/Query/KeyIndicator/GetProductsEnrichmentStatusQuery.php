<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\KeyIndicator;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfNonRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\GetProductsKeyIndicator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductsEnrichmentStatusQuery implements GetProductsKeyIndicator
{
    private const GOOD_ENRICHMENT_RATIO = 80;

    private Connection $db;

    private GetLocalesByChannelQueryInterface $getLocalesByChannelQuery;

    public function __construct(Connection $db, GetLocalesByChannelQueryInterface $getLocalesByChannelQuery)
    {
        $this->db = $db;
        $this->getLocalesByChannelQuery = $getLocalesByChannelQuery;
    }

    public function getName(): string
    {
        return 'good_enrichment';
    }

    public function execute(array $productIds): array
    {
        $productIdsByFamilyId = $this->groupProductsByFamily($productIds);

        //To handle a edge case where there is only product(s) without families
        if (empty($productIdsByFamilyId)) {
            return [];
        }

        $numberOfAttributesByFamilyId = $this->getNumberOfAttributesByFamilyId(array_keys($productIdsByFamilyId));

        $productsEnrichmentStatus = [];
        foreach ($productIdsByFamilyId as $familyId => $familyProductIds) {
            $productsEnrichmentStatus += $this->getProductEnrichmentStatusByChannelAndLocale($familyProductIds, $numberOfAttributesByFamilyId[$familyId] ?? 0);
        }

        return $productsEnrichmentStatus;
    }

    private function getProductEnrichmentStatusByChannelAndLocale(array $productIds, int $familyNumberOfAttributes): array
    {
        $localesByChannel = $this->getLocalesByChannelQuery->getArray();
        $numberOfEmptyValuesByProduct = $this->retrieveNumberOfEmptyValuesByProduct($productIds);

        $productEnrichmentStatus = [];
        foreach ($productIds as $productId) {
            $numberOfEmptyValues = $numberOfEmptyValuesByProduct[$productId] ?? [];
            foreach ($localesByChannel as $channel => $locales) {
                foreach ($locales as $locale) {
                    $productEnrichmentStatus[$productId][$channel][$locale] =
                        $this->computeEnrichmentRatioStatus($familyNumberOfAttributes, $numberOfEmptyValues[$channel.$locale] ?? null);
                }
            }
        }

        return $productEnrichmentStatus;
    }

    private function retrieveNumberOfEmptyValuesByProduct(array $productIds): array
    {
        $selectQueryParts = [];
        foreach ($this->getLocalesByChannelQuery->getArray() as $channel => $locales) {
            foreach ($locales as $locale) {
                $selectQueryParts[] = "SUM(JSON_LENGTH(JSON_EXTRACT(result, '$.data.attributes_with_rates.$channel.$locale'))) as $channel$locale";
            }
        }
        $fieldsQuery = join(',', $selectQueryParts);

        $query = <<<SQL
SELECT product_id, $fieldsQuery
FROM pim_data_quality_insights_product_criteria_evaluation
WHERE product_id IN(:productIds) AND criterion_code IN(:criterionCodes)
GROUP BY product_id;
SQL;

        $stmt = $this->db->executeQuery(
            $query,
            [
                'productIds' => $productIds,
                'criterionCodes' => [
                    EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE,
                    EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE,
                ]
            ],
            [
                'productIds' => Connection::PARAM_INT_ARRAY,
                'criterionCodes' => Connection::PARAM_STR_ARRAY,
            ]
        );

        $numberOfEmptyValuesByProduct = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $numberOfEmptyValuesByProduct[$row['product_id']] = $row;
        }

        return $numberOfEmptyValuesByProduct;
    }

    private function computeEnrichmentRatioStatus(int $familyNumberOfAttributes, $numberOfMissingAttributes): ?bool
    {
        if (null === $numberOfMissingAttributes || 0 === $familyNumberOfAttributes) {
            return null;
        }

        return ($familyNumberOfAttributes - $numberOfMissingAttributes) / $familyNumberOfAttributes * 100 >= self::GOOD_ENRICHMENT_RATIO;
    }

    private function getNumberOfAttributesByFamilyId(array $familyIds): array
    {
        $query = <<<SQL
SELECT family_id, count(attribute_id) number_of_attributes 
FROM pim_catalog_family_attribute
where family_id IN(:familyIds)
GROUP BY family_id
SQL;
        $rows = $this->db->executeQuery(
            $query,
            ['familyIds' => $familyIds],
            ['familyIds' => Connection::PARAM_INT_ARRAY]
        )->fetchAll(\PDO::FETCH_ASSOC);
        $numberOfAttributesByFamily = [];
        foreach ($rows as $row) {
            $numberOfAttributesByFamily[(int) $row['family_id']] = (int) $row['number_of_attributes'];
        }

        return $numberOfAttributesByFamily;
    }

    private function groupProductsByFamily(array $productIds): array
    {
        $productIds = array_map(fn (ProductId $productId) => $productId->toInt(), $productIds);

        $query = <<<SQL
SELECT JSON_OBJECTAGG(products_by_family.family_id, products_by_family.product_ids)
FROM (
    SELECT family_id, JSON_ARRAYAGG(product.id) AS product_ids
    FROM pim_catalog_product product
    INNER JOIN pim_catalog_family family ON(family.id = product.family_id)
    WHERE product.id IN (:productIds)
    AND family_id IS NOT NULL
    GROUP BY family_id
) as products_by_family
SQL;

        $result = $this->db->executeQuery($query, ['productIds' => $productIds], ['productIds' => Connection::PARAM_INT_ARRAY])->fetchColumn();
        if (!$result) {
            return [];
        }

        return json_decode($result, true);
    }
}
