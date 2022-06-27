<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\CalculateProductCompletenessInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class EvaluateCompleteness
{
    private GetLocalesByChannelQueryInterface $localesByChannelQuery;

    public function __construct(GetLocalesByChannelQueryInterface $localesByChannelQuery)
    {
        $this->localesByChannelQuery = $localesByChannelQuery;
    }

    public function evaluate(CalculateProductCompletenessInterface $completenessCalculator, Write\CriterionEvaluation $criterionEvaluation): Write\CriterionEvaluationResult
    {
        $localesByChannel = $this->localesByChannelQuery->getChannelLocaleCollection();
        $completenessResult = $completenessCalculator->calculate($criterionEvaluation->getEntityId());

        $evaluationResult = new Write\CriterionEvaluationResult();
        foreach ($localesByChannel as $channelCode => $localeCodes) {
            foreach ($localeCodes as $localeCode) {
                $this->evaluateChannelLocaleRate($evaluationResult, $channelCode, $localeCode, $completenessResult);
            }
        }

        return $evaluationResult;
    }

    private function evaluateChannelLocaleRate(
        Write\CriterionEvaluationResult $evaluationResult,
        ChannelCode $channelCode,
        LocaleCode $localeCode,
        Write\CompletenessCalculationResult $completenessResult
    ): void {
        $rate = $completenessResult->getRates()->getByChannelAndLocale($channelCode, $localeCode);

        if (null === $rate) {
            $evaluationResult->addStatus($channelCode, $localeCode, CriterionEvaluationResultStatus::notApplicable());
            return;
        }

        $missingAttributes = $completenessResult->getMissingAttributes()->getByChannelAndLocale($channelCode, $localeCode);
        $missingAttributesCount = null === $missingAttributes ? 0 : count($missingAttributes);

        $totalNumberOfAttributes = $completenessResult->getTotalNumberOfAttributes()->getByChannelAndLocale($channelCode, $localeCode);

        $evaluationResult
            ->addRate($channelCode, $localeCode, $rate)
            ->addStatus($channelCode, $localeCode, CriterionEvaluationResultStatus::done())
            ->addData('number_of_improvable_attributes', $channelCode, $localeCode, $missingAttributesCount)
            ->addData('total_number_of_attributes', $channelCode, $localeCode, $totalNumberOfAttributes)
        ;
    }
}
