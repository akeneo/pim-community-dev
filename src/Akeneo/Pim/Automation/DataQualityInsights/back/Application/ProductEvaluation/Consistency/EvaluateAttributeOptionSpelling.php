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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributeOptionSpellcheckQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\SpellCheckResult;

final class EvaluateAttributeOptionSpelling implements EvaluateCriterionInterface
{
    public const CRITERION_CODE = 'consistency_attribute_option_spelling';

    public const CRITERION_COEFFICIENT = 1;

    private $localesByChannelQuery;

    private $getAttributeOptionSpellcheckQuery;

    private $optionsByAttribute;

    private $optionsSpellchecksByAttribute;

    public function __construct(
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        GetAttributeOptionSpellcheckQueryInterface $getAttributeOptionSpellcheckQuery
    ) {
        $this->localesByChannelQuery = $localesByChannelQuery;
        $this->getAttributeOptionSpellcheckQuery = $getAttributeOptionSpellcheckQuery;
    }

    public function evaluate(Write\CriterionEvaluation $criterionEvaluation, ProductValuesCollection $productValues): Write\CriterionEvaluationResult
    {
        $localesByChannel = $this->localesByChannelQuery->getChannelLocaleCollection();

        $this->resetOptionsByAttribute();

        foreach ($localesByChannel as $channelCode => $localesCodes) {
            foreach ($localesCodes as $localeCode) {
                $this->buildOptionsListByAttribute($productValues->getSimpleSelectValues(), $channelCode, $localeCode);
                $this->buildOptionsListByAttribute($productValues->getMultiSelectValues(), $channelCode, $localeCode);
            }
        }

        foreach ($this->optionsByAttribute as $attributeCode => $optionCodes) {
            $spellChecks = $this->getAttributeOptionSpellcheckQuery->getByAttributeAndOptionCodes(new AttributeCode(strval($attributeCode)), $optionCodes);
            if (empty($spellChecks)) {
                continue;
            }
            $this->optionsSpellchecksByAttribute[$attributeCode] = $spellChecks;
        }

        $evaluationResult = new Write\CriterionEvaluationResult();

        foreach ($localesByChannel as $channelCode => $localesCodes) {
            foreach ($localesCodes as $localeCode) {
                $this->evaluateChannelLocaleRate($evaluationResult, $channelCode, $localeCode, $productValues);
            }
        }

        return $evaluationResult;
    }

    private function resetOptionsByAttribute(): void
    {
        $this->optionsByAttribute = [];
        $this->optionsSpellchecksByAttribute = [];
    }

    private function buildOptionsListByAttribute(\Iterator $productValues, ChannelCode $channelCode, LocaleCode $localeCode)
    {
        foreach ($productValues as $productValueByChannelAndLocale) {
            $attributeCode = strval($productValueByChannelAndLocale->getAttribute()->getCode());
            $optionCodes = $productValueByChannelAndLocale->getValueByChannelAndLocale($channelCode, $localeCode);

            if (empty($optionCodes)) {
                continue;
            }
            if (is_string($optionCodes)) {
                $optionCodes = [$optionCodes];
            }

            if (array_key_exists($attributeCode, $this->optionsByAttribute)) {
                $this->optionsByAttribute[$attributeCode] = array_unique(array_merge($this->optionsByAttribute[$attributeCode], $optionCodes));
            } else {
                $this->optionsByAttribute[$attributeCode] = $optionCodes;
            }
        }
    }

    private function evaluateChannelLocaleRate(Write\CriterionEvaluationResult $evaluationResult, ChannelCode $channelCode, LocaleCode $localeCode, ProductValuesCollection $productValues): void
    {
        $simpleSelectOptionCodes = $productValues->getSimpleSelectValues();
        $simpleSelectRates = $this->evaluateSimpleSelectAttributesRates($channelCode, $localeCode, $simpleSelectOptionCodes);

        $multiSelectOptionCodes = $productValues->getMultiSelectValues();
        $multiSelectRates = $this->evaluateMultiSelectAttributesRates($channelCode, $localeCode, $multiSelectOptionCodes);

        $attributesRates = array_merge($simpleSelectRates, $multiSelectRates);

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

    private function evaluateSimpleSelectAttributesRates(ChannelCode $channelCode, LocaleCode $localeCode, \Iterator $productValues): array
    {
        $attributesRates = [];
        foreach ($productValues as $productValueByChannelAndLocale) {
            $attributeCode = strval($productValueByChannelAndLocale->getAttribute()->getCode());
            $optionCode = $productValueByChannelAndLocale->getValueByChannelAndLocale($channelCode, $localeCode);

            if ($optionCode === null || $optionCode === '') {
                continue;
            }

            if (! isset($this->optionsSpellchecksByAttribute[$attributeCode][$optionCode])) {
                continue;
            }

            $spellCheck = $this->optionsSpellchecksByAttribute[$attributeCode][$optionCode];
            $spellCheckResult = $spellCheck->getResult()->getLocaleResult($localeCode);
            if (!$spellCheckResult instanceof SpellCheckResult) {
                continue;
            }

            $attributesRates[$attributeCode] = new Rate($spellCheckResult->isToImprove() ? 0 : 100);
        }

        return $attributesRates;
    }

    private function evaluateMultiSelectAttributesRates(ChannelCode $channelCode, LocaleCode $localeCode, \Iterator $productValues): array
    {
        $attributesRates = [];
        foreach ($productValues as $productValueByChannelAndLocale) {
            $attributeCode = strval($productValueByChannelAndLocale->getAttribute()->getCode());
            $optionCodes = $productValueByChannelAndLocale->getValueByChannelAndLocale($channelCode, $localeCode);

            if (empty($optionCodes)) {
                continue;
            }

            $attributeTotalRate = 0;
            $optionNumber = 0;
            foreach ($optionCodes as $optionCode) {
                if (! isset($this->optionsSpellchecksByAttribute[$attributeCode][$optionCode])) {
                    continue;
                }
                $spellCheck = $this->optionsSpellchecksByAttribute[$attributeCode][$optionCode];
                $spellCheckResult = $spellCheck->getResult()->getLocaleResult($localeCode);
                if (!$spellCheckResult instanceof SpellCheckResult) {
                    continue;
                }
                $attributeTotalRate += $spellCheckResult->isToImprove() ? 0 : 100;
                $optionNumber += 1;
            }

            if (0 === $optionNumber) {
                continue;
            }

            $rate = round($attributeTotalRate / $optionNumber, 0, PHP_ROUND_HALF_DOWN);
            $attributesRates[$attributeCode] = new Rate((int) $rate);
        }

        return $attributesRates;
    }

    private function getRateByAttributes(array $attributesRates): array
    {
        return array_map(function (Rate $attributeRate) {
            return $attributeRate->toInt();
        }, $attributesRates);
    }

    private function calculateChannelLocaleRate(array $channelLocaleRates): Rate
    {
        return new Rate((int) round(array_sum($channelLocaleRates) / count($channelLocaleRates), 0, PHP_ROUND_HALF_DOWN));
    }

    public function getCode(): CriterionCode
    {
        return new CriterionCode(self::CRITERION_CODE);
    }

    public function getCoefficient(): int
    {
        return self::CRITERION_COEFFICIENT;
    }
}
