<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\CalculateProductCompletenessInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfNonRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CompletenessCalculationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * The aim of this service is to complete the evaluation of a product (or product model)
 *  with the lists of improvable attributes that are not persisted in the database.
 * For now, only the two completeness criteria are concerned.
 * If there are more criteria to add, this service should be reworked with a registry.
 */
class CompleteEvaluationWithImprovableAttributes
{
    public function __construct(
        private GetLocalesByChannelQueryInterface $localesByChannelQuery,
        private CalculateProductCompletenessInterface $calculateRequiredAttributesCompleteness,
        private CalculateProductCompletenessInterface $calculateNonRequiredAttributesCompleteness
    ) {
    }

    public function __invoke(Read\CriterionEvaluationCollection $criterionEvaluationCollection): Read\CriterionEvaluationCollection
    {
        $criterionEvaluationCollection = $this->completeCompletenessEvaluation(
            new CriterionCode(EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE),
            $criterionEvaluationCollection,
            $this->calculateRequiredAttributesCompleteness
        );

        return $this->completeCompletenessEvaluation(
            new CriterionCode(EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE),
            $criterionEvaluationCollection,
            $this->calculateNonRequiredAttributesCompleteness
        );
    }

    private function completeCompletenessEvaluation(
        CriterionCode $criterionCode,
        Read\CriterionEvaluationCollection $criterionEvaluationCollection,
        CalculateProductCompletenessInterface $calculateCompleteness
    ): Read\CriterionEvaluationCollection {
        $criterionEvaluation = $criterionEvaluationCollection->get($criterionCode);

        if (null === $criterionEvaluation) {
            return $criterionEvaluationCollection;
        }

        $completenessResult = $calculateCompleteness->calculate($criterionEvaluation->getProductId());

        $evaluationResultData = $criterionEvaluation->getResult()->getData();
        $evaluationResultData['attributes_with_rates'] = $this->getAttributesWithRates($completenessResult);

        /**
         * In some cases the rates of the persisted result are different from the one just calculated (See PIM-10967)
         * It can happen when the required attributes list of a family has been changed, but the impacted products have not been re-evaluated yet
         * So we also need to replace the rates to be always accurate with the list of improvable attributes
         */
        $completedCriterionEvaluationResult = new Read\CriterionEvaluationResult(
            $completenessResult->getRates(),
            $criterionEvaluation->getResult()->getStatus(),
            $evaluationResultData
        );

        $completedCriterionEvaluation = new Read\CriterionEvaluation(
            $criterionEvaluation->getCriterionCode(),
            $criterionEvaluation->getProductId(),
            $criterionEvaluation->getEvaluatedAt(),
            $criterionEvaluation->getStatus(),
            $completedCriterionEvaluationResult
        );

        return $criterionEvaluationCollection->add($completedCriterionEvaluation);
    }

    private function getAttributesWithRates(CompletenessCalculationResult $completenessResult): array
    {
        $localesByChannel = $this->localesByChannelQuery->getChannelLocaleCollection();
        $evaluationResultData = [];

        foreach ($localesByChannel as $channelCode => $localeCodes) {
            foreach ($localeCodes as $localeCode) {
                $missingAttributes = $completenessResult->getMissingAttributes()->getByChannelAndLocale($channelCode, $localeCode);

                $attributesRates = [];
                if (null !== $missingAttributes) {
                    foreach ($missingAttributes as $attributeCode) {
                        $attributesRates[$attributeCode] = 0;
                    }
                }
                $evaluationResultData[\strval($channelCode)][\strval($localeCode)] = $attributesRates;
            }
        }

        return $evaluationResultData;
    }
}
