<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\KeyIndicator;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfNonRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\KeyIndicator\ProductsWithGoodEnrichment;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\ComputeProductsKeyIndicator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductsEvaluationsDataByCriterionInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
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

    public function __construct(
        private Connection                        $db,
        private GetLocalesByChannelQueryInterface $getLocalesByChannelQuery,
        private Channels                          $channels,
        private Locales                           $locales,
        private GetProductsEvaluationsDataByCriterionInterface $getProductsEvaluationsDataByCriterion
    ) {
    }

    public function getName(): string
    {
        return ProductsWithGoodEnrichment::CODE;
    }

    public function compute(ProductIdCollection $productIdCollection): array
    {
        $channelsLocales = $this->getLocalesByChannelQuery->getArray();
        $productIds = $productIdCollection->toArrayInt();
        $productsEvaluations = $this->getProductsEvaluations($productIds);
        $productsEnrichmentStatus = [];
        foreach ($productIds as $productId) {
            if (!isset($productsEvaluations[$productId])) {
                continue;
            }

            $productsEnrichmentStatus[$productId] = $this->computeForChannelsLocales($productsEvaluations[$productId], $channelsLocales);
        }

        return $productsEnrichmentStatus;
    }

    private function computeForChannelsLocales(array $evaluations, array $channelsLocales): array
    {
        $enrichmentStatus = [];
        foreach ($channelsLocales as $channel => $locales) {
            $channelId = $this->channels->getIdByCode($channel);
            if (null === $channelId) {
                continue;
            }

            foreach ($locales as $locale) {
                $localeId = $this->locales->getIdByCode($locale);
                if (null === $localeId) {
                    continue;
                }

                $enrichmentStatus[$channel][$locale] = $this->computeEnrichmentStatus($evaluations, $channelId, $localeId);
            }
        }

        return $enrichmentStatus;
    }

    private function computeEnrichmentStatus(array $evaluations, int $channelId, int $localeId): ?bool
    {

        $nonRequiredAttributesEvaluation = $evaluations[EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE] ?? [];
        $requiredAttributesEvaluation = $evaluations[EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE] ?? [];

        //Handle the products without family (so the completeness couldn't be calculated)
        if (
            !isset($nonRequiredAttributesEvaluation['number_of_improvable_attributes'][$channelId][$localeId]) ||
            !isset($requiredAttributesEvaluation['number_of_improvable_attributes'][$channelId][$localeId]) ||
            !isset($nonRequiredAttributesEvaluation['total_number_of_attributes'][$channelId][$localeId]) ||
            !isset($requiredAttributesEvaluation['total_number_of_attributes'][$channelId][$localeId])
        ) {
            return null;
        }

        $missingNonRequiredAttributesNumber = $nonRequiredAttributesEvaluation['number_of_improvable_attributes'][$channelId][$localeId];
        $missingRequiredAttributesNumber = $requiredAttributesEvaluation['number_of_improvable_attributes'][$channelId][$localeId];
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
        $evaluations = [];
        foreach ($this->getProductsEvaluationsDataByCriterion->execute($criterionCode, $productIds) as $evaluation) {
            $evaluationResult = isset($evaluation['result']) ? json_decode($evaluation['result'], true) : null;
            $evaluationResultData = $evaluationResult[TransformCriterionEvaluationResultCodes::PROPERTIES_ID['data']] ?? [];

            $evaluations[$evaluation['product_id']] = [
                'total_number_of_attributes' => $evaluationResultData[TransformCriterionEvaluationResultCodes::DATA_TYPES_ID['total_number_of_attributes']] ?? 0,
            ];

            if (isset($evaluationResultData[TransformCriterionEvaluationResultCodes::DATA_TYPES_ID['number_of_improvable_attributes']])) {
                $evaluations[$evaluation['product_id']]['number_of_improvable_attributes'] = $evaluationResultData[TransformCriterionEvaluationResultCodes::DATA_TYPES_ID['number_of_improvable_attributes']];
            } elseif (isset($evaluationResultData[TransformCriterionEvaluationResultCodes::DATA_TYPES_ID['attributes_with_rates']])) {
                // The data 'attributes_with_rates' is deprecated, but can still exist because of no migration data. (See PLG-468)
                $evaluations[$evaluation['product_id']]['number_of_improvable_attributes'] = count($evaluationResultData[TransformCriterionEvaluationResultCodes::DATA_TYPES_ID['attributes_with_rates']]);
            }


        }

        return $evaluations;
    }
}
