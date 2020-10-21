<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\KeyIndicator;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleDataScalarCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\GetProductsKeyIndicator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Doctrine\DBAL\Connection;

final class GetProductsEnrichmentStatusQuery implements GetProductsKeyIndicator
{
    private const GOOD_ENRICHEMENT_RATIO = 80;

    private $db;

    private $getLocalesByChannelQuery;

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
        $localesByChannel = $this->getLocalesByChannelQuery->getArray();
        $productIdsByFamilyId = $this->groupProductsByFamily($productIds);

        //To handle a edge case where there is only product(s) without families
        if (empty($productIdsByFamilyId)) {
            return [];
        }

        $numberOfAttributesByFamilyId = $this->getNumberOfAttributesByFamilyId(array_keys($productIdsByFamilyId));

        $result = [];
        foreach ($productIdsByFamilyId as $familyId => $familyProductIds) {
            $result += $this->getProductEnrichmentStatusByChannelAndLocale($familyProductIds, $localesByChannel, $numberOfAttributesByFamilyId[$familyId]);
        }

        return $result;
    }

    private function getProductEnrichmentStatusByChannelAndLocale(array $productIds, array $localesByChannel, int $familyNumberOfAttributes): array
    {
        $query = $this->buildQuery($localesByChannel);

        $stmt = $this->db->executeQuery($query, ['productIds' => $productIds], ['productIds' => Connection::PARAM_INT_ARRAY]);
        $productsResults = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $productsResults[$row['product_id']] = $row;
        }

        $ProductStatus = [];
        foreach ($productIds as $productId) {
            $productResults = $productsResults[$productId] ?? [];
            $channelLocaleStatus = ChannelLocaleDataScalarCollection::filledWith($localesByChannel, function ($channel, $locale) use ($productResults, $familyNumberOfAttributes) {
                $numberOfAttributesWithNoValue = $productResults[$channel.$locale] ?? null;

                return null !== $numberOfAttributesWithNoValue ? $this->computeEnrichmentRatioStatus($familyNumberOfAttributes, $numberOfAttributesWithNoValue) : null;
            });

            $ProductStatus[$productId] = $channelLocaleStatus->toArray();
        }

        return $ProductStatus;
    }

    private function computeEnrichmentRatioStatus(int $familyNumberOfAttributes, int $numberOfMissingAttributes): bool
    {
        return ($familyNumberOfAttributes - $numberOfMissingAttributes) / $familyNumberOfAttributes * 100 >= self::GOOD_ENRICHEMENT_RATIO;
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

    //Could be extracted in a separated query if necessary (POC Laurent)
    private function groupProductsByFamily(array $productIds): array
    {
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

    private function buildQuery(array $localesByChannel): string
    {
        $selectQueryParts = [];
        foreach ($localesByChannel as $channel => $locales) {
            foreach ($locales as $locale) {
                $selectQueryParts[] = "SUM(JSON_LENGTH(JSON_EXTRACT(result, '$.data.attributes_with_rates.$channel.$locale'))) as $channel$locale";
            }
        }
        $fieldsQuery = join(',', $selectQueryParts);

        return <<<SQL
SELECT product_id, $fieldsQuery
FROM pim_data_quality_insights_product_criteria_evaluation
WHERE product_id IN(:productIds)
AND criterion_code IN('completeness_of_non_required_attributes', 'completeness_of_required_attributes')
GROUP BY product_id;
SQL;
    }
}
