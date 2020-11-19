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

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateCriterionInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Criterion\LowerCaseWords;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValues;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;

final class EvaluateLowerCaseWords implements EvaluateCriterionInterface
{
    /** @var GetLocalesByChannelQueryInterface */
    private $localesByChannelQuery;

    /** @var LowerCaseWords */
    private $criterion;

    public function __construct(GetLocalesByChannelQueryInterface $localesByChannelQuery)
    {
        $this->localesByChannelQuery = $localesByChannelQuery;
        $this->criterion = new LowerCaseWords();
    }

    public function getCode(): CriterionCode
    {
        return $this->criterion->getCode();
    }

    public function getCoefficient(): int
    {
        return LowerCaseWords::CRITERION_COEFFICIENT;
    }

    public function evaluate(Write\CriterionEvaluation $criterionEvaluation, ProductValuesCollection $productValues): Write\CriterionEvaluationResult
    {
        $localesByChannel = $this->localesByChannelQuery->getChannelLocaleCollection();

        $evaluationResult = new Write\CriterionEvaluationResult();
        foreach ($localesByChannel as $channelCode => $localeCodes) {
            foreach ($localeCodes as $localeCode) {
                $this->evaluateChannelLocaleRate($evaluationResult, $channelCode, $localeCode, $productValues);
            }
        }

        return $evaluationResult;
    }

    private function evaluateChannelLocaleRate(Write\CriterionEvaluationResult $evaluationResult, ChannelCode $channelCode, LocaleCode $localeCode, ProductValuesCollection $productValues): void
    {
        $attributesRates = [];
        /** @var ProductValues $productValueByChannelAndLocale */
        foreach ($productValues->getTextareaValues() as $productValueByChannelAndLocale) {
            $attributeCode = $productValueByChannelAndLocale->getAttribute()->getCode();
            $productValue = $productValueByChannelAndLocale->getValueByChannelAndLocale($channelCode, $localeCode);
            $rate = $this->computeProductValueRate($productValue);

            if ($rate !== null) {
                $attributesRates[strval($attributeCode)] = $rate;
            }
        }

        if (empty($attributesRates)) {
            $evaluationResult->addStatus($channelCode, $localeCode, CriterionEvaluationResultStatus::notApplicable());
            return;
        }

        $rateByAttributes = $this->getRateByAttributes($attributesRates);
        $rate = $this->calculateChannelLocaleRate($rateByAttributes);

        $evaluationResult
            ->addStatus($channelCode, $localeCode, CriterionEvaluationResultStatus::done())
            ->addRate($channelCode, $localeCode, $rate)
            ->addRateByAttributes($channelCode, $localeCode, $rateByAttributes);
    }

    private function computeProductValueRate(?string $productValue): ?Rate
    {
        if ($productValue === null) {
            return null;
        }

        $productValue = strip_tags($productValue);

        if (trim($productValue) === '') {
            return null;
        }

        return $this->criterion->evaluate($productValue);
    }

    private function calculateChannelLocaleRate(array $channelLocaleRates): Rate
    {
        return new Rate((int) round(array_sum($channelLocaleRates) / count($channelLocaleRates), 0, PHP_ROUND_HALF_DOWN));
    }

    private function getRateByAttributes(array $attributesRates): array
    {
        return array_map(function (Rate $attributeRate) {
            return $attributeRate->toInt();
        }, $attributesRates);
    }
}
