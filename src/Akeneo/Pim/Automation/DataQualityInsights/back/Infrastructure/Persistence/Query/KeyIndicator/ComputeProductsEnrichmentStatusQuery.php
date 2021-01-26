<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\KeyIndicator;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfNonRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\KeyIndicator\ProductsWithGoodEnrichment;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\ComputeProductsKeyIndicator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Channels;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Locales;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\TransformCriterionEvaluationResultCodes;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ComputeProductsEnrichmentStatusQuery implements ComputeProductsKeyIndicator
{
    private const GOOD_ENRICHMENT_RATIO = 80;

    private Connection $db;

    private GetLocalesByChannelQueryInterface $getLocalesByChannelQuery;

    private Channels $channels;

    private Locales $locales;

    public function __construct(
        Connection $db,
        GetLocalesByChannelQueryInterface $getLocalesByChannelQuery,
        Channels $channels,
        Locales $locales
    ) {
        $this->db = $db;
        $this->getLocalesByChannelQuery = $getLocalesByChannelQuery;
        $this->channels = $channels;
        $this->locales = $locales;
    }

    public function getName(): string
    {
        return ProductsWithGoodEnrichment::CODE;
    }

    public function compute(array $productIds): array
    {
        $productIds = array_map(fn (ProductId $productId) => $productId->toInt(), $productIds);
        $localesByChannel = $this->getLocalesByChannelQuery->getArray();
        $productsEvaluations = $this->getProductsEvaluations($productIds);

        $productsEnrichmentStatus = [];
        foreach ($productIds as $productId) {
            foreach ($localesByChannel as $channel => $locales) {
                $channelId = $this->channels->getIdByCode($channel);
                foreach ($locales as $locale) {
                    $localeId = $this->locales->getIdByCode($locale);
                    $productsEnrichmentStatus[$productId][$channel][$locale] = isset($productsEvaluations[$productId])
                        ? $this->computeProductEnrichmentStatus($productsEvaluations[$productId], $channelId, $localeId)
                        : null;
                }
            }
        }

        return $productsEnrichmentStatus;
    }

    private function computeProductEnrichmentStatus(array $evaluations, int $channelId, int $localeId): ?bool
    {
        $nonRequiredAttributesEvaluation = $evaluations[EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE] ?? [];
        $requiredAttributesEvaluation = $evaluations[EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE] ?? [];

        //Handle the products without family (so the completeness couldn't be calculated)
        if (
            !isset($nonRequiredAttributesEvaluation['attributes_with_rates'][$channelId][$localeId]) ||
            !isset($requiredAttributesEvaluation['attributes_with_rates'][$channelId][$localeId]) ||
            !isset($nonRequiredAttributesEvaluation['total_number_of_attributes'][$channelId][$localeId]) ||
            !isset($requiredAttributesEvaluation['total_number_of_attributes'][$channelId][$localeId])
        ) {
            return null;
        }

        $missingNonRequiredAttributesNumber = count($nonRequiredAttributesEvaluation['attributes_with_rates'][$channelId][$localeId]);
        $missingRequiredAttributesNumber = count($requiredAttributesEvaluation['attributes_with_rates'][$channelId][$localeId]);

        $numberOfNonRequiredAttributes = $nonRequiredAttributesEvaluation['total_number_of_attributes'][$channelId][$localeId];
        $numberOfRequiredAttributes = $requiredAttributesEvaluation['total_number_of_attributes'][$channelId][$localeId];

        return $this->computeEnrichmentRatioStatus($numberOfNonRequiredAttributes + $numberOfRequiredAttributes, $missingNonRequiredAttributesNumber + $missingRequiredAttributesNumber);
    }

    private function computeEnrichmentRatioStatus(int $familyNumberOfAttributes, $numberOfMissingAttributes): bool
    {
        if ($familyNumberOfAttributes === 0) {
            return true;
        }

        return ($familyNumberOfAttributes - $numberOfMissingAttributes) / $familyNumberOfAttributes * 100 >= self::GOOD_ENRICHMENT_RATIO;
    }

    private function getProductsEvaluations(array $productIds): array
    {
        $requiredAttributesEvaluations = $this->getProductsEvaluationsByCriterion(EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE, $productIds);
        $nonRequiredAttributesEvaluations = $this->getProductsEvaluationsByCriterion(EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE, $productIds);

        $productsEvaluations = [];
        foreach ($productIds as $productId) {
            $productsEvaluations[$productId] = [
                EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE => $requiredAttributesEvaluations[$productId] ?? null,
                EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE => $nonRequiredAttributesEvaluations[$productId] ?? null,
            ];
        }

        return $productsEvaluations;
    }

    private function getProductsEvaluationsByCriterion(string $criterionCode, array $productIds): array
    {
        $query = <<<SQL
SELECT product_id, result
FROM pim_data_quality_insights_product_criteria_evaluation
WHERE product_id IN(:productIds) AND criterion_code = :criterionCode
SQL;

        $stmt = $this->db->executeQuery(
            $query,
            [
                'productIds' => $productIds,
                'criterionCode' => $criterionCode,
            ],
            [
                'productIds' => Connection::PARAM_INT_ARRAY,
            ]
        );

        $evaluations = [];
        while ($evaluation = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $evaluationResult = isset($evaluation['result']) ? json_decode($evaluation['result'], true) : null;
            $evaluations[$evaluation['product_id']] = is_array($evaluation)
                ? [
                    'attributes_with_rates' => $evaluationResult[TransformCriterionEvaluationResultCodes::PROPERTIES_ID['data']][TransformCriterionEvaluationResultCodes::DATA_TYPES_ID['attributes_with_rates']] ?? [],
                    'total_number_of_attributes' => $evaluationResult[TransformCriterionEvaluationResultCodes::PROPERTIES_ID['data']][TransformCriterionEvaluationResultCodes::DATA_TYPES_ID['total_number_of_attributes']] ?? 0
                ]
                : null;
        }

        return $evaluations;
    }
}
