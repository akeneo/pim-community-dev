<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateCriterionInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;

final class EvaluateUppercaseWords implements EvaluateCriterionInterface
{
    public const CRITERION_CODE = 'consistency_textarea_uppercase_words';

    public const CRITERION_COEFFICIENT = 1;

    /** @var GetLocalesByChannelQueryInterface */
    private $localesByChannelQuery;

    public function __construct(GetLocalesByChannelQueryInterface $localesByChannelQuery)
    {
        $this->localesByChannelQuery = $localesByChannelQuery;
    }

    public function getCode(): CriterionCode
    {
        return new CriterionCode(self::CRITERION_CODE);
    }

    public function getCoefficient(): int
    {
        return self::CRITERION_COEFFICIENT;
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

    private function getRateByAttributes(array $attributesRates): array
    {
        return array_map(function (Rate $attributeRate) {
            return $attributeRate->toInt();
        }, $attributesRates);
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

        $anyKindOfLetterFromAnyLanguageRegex = '~\p{L}+~u';
        if (preg_match($anyKindOfLetterFromAnyLanguageRegex, $productValue) === 0) {
            return new Rate(100);
        }

        return new Rate(mb_strtoupper($productValue) === $productValue ? 0 : 100);
    }

    private function calculateChannelLocaleRate(array $attributesRates): Rate
    {
        return new Rate((int) round(array_sum($attributesRates) / count($attributesRates), 0, PHP_ROUND_HALF_DOWN));
    }
}
