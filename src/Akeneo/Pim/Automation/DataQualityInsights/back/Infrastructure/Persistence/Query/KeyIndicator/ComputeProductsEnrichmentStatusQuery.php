<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\KeyIndicator;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfNonRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\KeyIndicator\ProductsWithGoodEnrichment;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\ComputeProductsKeyIndicator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\TransformCriterionEvaluationResultIds;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\ResultStatement;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ComputeProductsEnrichmentStatusQuery implements ComputeProductsKeyIndicator
{
    private const GOOD_ENRICHMENT_RATIO = 80;

    private Connection $db;

    private GetLocalesByChannelQueryInterface $getLocalesByChannelQuery;

    private TransformCriterionEvaluationResultIds $transformCriterionEvaluationResultIds;

    public function __construct(
        Connection $db,
        GetLocalesByChannelQueryInterface $getLocalesByChannelQuery,
        TransformCriterionEvaluationResultIds $transformCriterionEvaluationResultIds
    ) {
        $this->db = $db;
        $this->getLocalesByChannelQuery = $getLocalesByChannelQuery;
        $this->transformCriterionEvaluationResultIds = $transformCriterionEvaluationResultIds;
    }

    public function getName(): string
    {
        return ProductsWithGoodEnrichment::CODE;
    }

    public function compute(array $productIds): array
    {
        $productIds = array_map(fn (ProductId $productId) => $productId->toInt(), $productIds);

        $localesByChannel = $this->getLocalesByChannelQuery->getArray();

        $productsEnrichmentStatus = [];
        foreach ($productIds as $productId) {
            foreach ($localesByChannel as $channel => $locales) {
                foreach ($locales as $locale) {
                    $productsEnrichmentStatus[$productId][$channel][$locale] = null;
                }
            }
        }

        $stmt = $this->getProductsEvaluations($productIds);

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $evaluationResults = json_decode($row['results'], true);
            $evaluationResults = array_map(
                fn ($results) => is_array($results) ? $this->transformCriterionEvaluationResultIds->transformToCodes($results) : null,
                $evaluationResults
            );
            $productsEnrichmentStatus[$row['product_id']] = $this->computeProductEnrichmentStatus($evaluationResults, $localesByChannel);
        }

        return $productsEnrichmentStatus;
    }

    private function computeProductEnrichmentStatus(array $evaluations, $localesByChannel): array
    {
        $nonRequiredAttributesEvaluation = $evaluations[EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE] ?? [];
        $requiredAttributesEvaluation = $evaluations[EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE] ?? [];

        $result = [];
        foreach ($localesByChannel as $channel => $locales) {
            foreach ($locales as $locale) {
                //Handle the products without family (so the completeness couldn't be calculated)
                if (
                    !isset($nonRequiredAttributesEvaluation['data']['attributes_with_rates'][$channel][$locale]) ||
                    !isset($requiredAttributesEvaluation['data']['attributes_with_rates'][$channel][$locale]) ||
                    !isset($nonRequiredAttributesEvaluation['data']['total_number_of_attributes'][$channel][$locale]) ||
                    !isset($requiredAttributesEvaluation['data']['total_number_of_attributes'][$channel][$locale])
                ) {
                    $result[$channel][$locale] = null;
                    continue;
                }

                $missingNonRequiredAttributesNumber = count($nonRequiredAttributesEvaluation['data']['attributes_with_rates'][$channel][$locale]);
                $missingRequiredAttributesNumber = count($requiredAttributesEvaluation['data']['attributes_with_rates'][$channel][$locale]);

                $numberOfNonRequiredAttributes = $nonRequiredAttributesEvaluation['data']['total_number_of_attributes'][$channel][$locale];
                $numberOfRequiredAttributes = $requiredAttributesEvaluation['data']['total_number_of_attributes'][$channel][$locale];

                $result[$channel][$locale] = $this->computeEnrichmentRatioStatus($numberOfNonRequiredAttributes + $numberOfRequiredAttributes, $missingNonRequiredAttributesNumber + $missingRequiredAttributesNumber);
            }
        }

        return $result;
    }

    private function computeEnrichmentRatioStatus(int $familyNumberOfAttributes, $numberOfMissingAttributes): bool
    {
        if ($familyNumberOfAttributes === 0) {
            return true;
        }

        return ($familyNumberOfAttributes - $numberOfMissingAttributes) / $familyNumberOfAttributes * 100 >= self::GOOD_ENRICHMENT_RATIO;
    }

    private function getProductsEvaluations(array $productIds): ResultStatement
    {
        $query = <<<SQL
SELECT product_id, JSON_OBJECTAGG(criterion_code, result) as results
FROM pim_data_quality_insights_product_criteria_evaluation
WHERE product_id IN(:productIds) AND criterion_code IN(:criterionCodes)
GROUP BY product_id
SQL;

        return $this->db->executeQuery(
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
    }
}
