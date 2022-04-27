<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\KeyIndicator;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfNonRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\KeyIndicator\ProductsWithGoodEnrichment;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\ComputeProductsKeyIndicator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetEvaluationResultsByProductsAndCriterionQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\KeyIndicatorCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ComputeProductsEnrichmentStatusQuery implements ComputeProductsKeyIndicator
{
    private const GOOD_ENRICHMENT_RATIO = 80;

    public function __construct(
        private GetLocalesByChannelQueryInterface                        $getLocalesByChannelQuery,
        private GetEvaluationResultsByProductsAndCriterionQueryInterface $getEvaluationResultsByProductsAndCriterionQuery,
    ) {
    }

    public function getCode(): KeyIndicatorCode
    {
        return new KeyIndicatorCode(ProductsWithGoodEnrichment::CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function compute(ProductEntityIdCollection $entityIdCollection): array
    {
        $channelsLocales = $this->getLocalesByChannelQuery->getArray();
        $productsEvaluationResults = $this->getProductsCompletenessResults($entityIdCollection);
        $productsEnrichmentStatus = [];
        foreach ($entityIdCollection->toArrayString() as $entityId) {
            $productsEnrichmentStatus[$entityId] = $this->computeForChannelsLocales($productsEvaluationResults[$entityId], $channelsLocales);
        }

        return $productsEnrichmentStatus;
    }

    private function computeForChannelsLocales(array $evaluations, array $channelsLocales): array
    {
        $enrichmentStatus = [];
        foreach ($channelsLocales as $channel => $locales) {
            foreach ($locales as $locale) {
                $enrichmentStatus[$channel][$locale] = $this->computeEnrichmentStatus(
                    $evaluations[EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE] ?? null,
                    $evaluations[EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE] ?? null,
                    $channel,
                    $locale
                );
            }
        }

        return $enrichmentStatus;
    }

    private function computeEnrichmentStatus(
        ?CriterionEvaluationResult $nonRequiredAttributesEvaluationResult,
        ?CriterionEvaluationResult $requiredAttributesEvaluationResult,
        string $channel,
        string $locale
    ): ?bool {
        $nonRequiredAttributesEvaluation = null !== $nonRequiredAttributesEvaluationResult ? $nonRequiredAttributesEvaluationResult->getData() : [];
        $requiredAttributesEvaluationData = null !== $requiredAttributesEvaluationResult ? $requiredAttributesEvaluationResult->getData() : [];

        $totalNumberOfAttributes =
            ($nonRequiredAttributesEvaluation['total_number_of_attributes'][$channel][$locale] ?? 0)
            + ($requiredAttributesEvaluationData['total_number_of_attributes'][$channel][$locale] ?? 0);

        // It can happen when the product has not been evaluated yet, or when all the attributes are deactivated, or when a product doesn't have a family.
        if (0 === $totalNumberOfAttributes) {
            return null;
        }

        $numberOfMissingAttributes =
            ($nonRequiredAttributesEvaluation['number_of_improvable_attributes'][$channel][$locale] ?? 0)
            + ($requiredAttributesEvaluationData['number_of_improvable_attributes'][$channel][$locale] ?? 0);

        $enrichmentRatio = ($totalNumberOfAttributes - $numberOfMissingAttributes) / $totalNumberOfAttributes * 100;

        return $enrichmentRatio >= self::GOOD_ENRICHMENT_RATIO;
    }

    private function getProductsCompletenessResults(ProductEntityIdCollection $entityIds): array
    {
        $requiredAttributesEvaluations = $this->getEvaluationResultsByProductsAndCriterionQuery->execute(
            $entityIds,
            new CriterionCode(EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE)
        );
        $nonRequiredAttributesEvaluations = $this->getEvaluationResultsByProductsAndCriterionQuery->execute(
            $entityIds,
            new CriterionCode(EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE),
        );

        $productsEvaluations = [];
        foreach ($entityIds->toArrayString() as $productId) {
            $productsEvaluations[$productId] = [
                EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE => $requiredAttributesEvaluations[$productId] ?? null,
                EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE => $nonRequiredAttributesEvaluations[$productId] ?? null,
            ];
        }

        return $productsEvaluations;
    }
}
