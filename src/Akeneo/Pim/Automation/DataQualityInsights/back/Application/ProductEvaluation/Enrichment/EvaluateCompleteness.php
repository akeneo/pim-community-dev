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

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\CalculateProductCompletenessInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;

final class EvaluateCompleteness
{
    /** @var GetLocalesByChannelQueryInterface */
    private $localesByChannelQuery;

    public function __construct(GetLocalesByChannelQueryInterface $localesByChannelQuery)
    {
        $this->localesByChannelQuery = $localesByChannelQuery;
    }

    public function evaluate(CalculateProductCompletenessInterface $completenessCalculator, Write\CriterionEvaluation $criterionEvaluation): Write\CriterionEvaluationResult
    {
        $localesByChannel = $this->localesByChannelQuery->getChannelLocaleCollection();
        $completenessResult = $completenessCalculator->calculate($criterionEvaluation->getProductId());

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

        $attributesRates = [];

        if (null !== $missingAttributes) {
            foreach ($missingAttributes as $attributeCode) {
                $attributesRates[$attributeCode] = 0;
            }
        }

        $evaluationResult
            ->addRate($channelCode, $localeCode, $rate)
            ->addStatus($channelCode, $localeCode, CriterionEvaluationResultStatus::done())
            ->addRateByAttributes($channelCode, $localeCode, $attributesRates)
        ;
    }
}
