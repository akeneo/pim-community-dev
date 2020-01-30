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

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency;

use Akeneo\Pim\Automation\DataQualityInsights\Application\BuildProductValuesInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\EvaluateCriterionInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckResultCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetTextareaAttributeCodesCompatibleWithSpellingQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetTextAttributeCodesCompatibleWithSpellingQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class EvaluateSpelling implements EvaluateCriterionInterface
{
    const CRITERION_CODE = 'consistency_spelling';

    const TEXT_FAULT_WEIGHT = 24;
    const TEXTAREA_FAULT_WEIGHT = 12;

    /** @var TextChecker */
    private $textChecker;

    /** @var BuildProductValuesInterface */
    private $buildProductValues;

    /** @var GetLocalesByChannelQueryInterface */
    private $localesByChannelQuery;

    /** @var SupportedLocaleChecker */
    private $supportedLocaleChecker;

    /** @var GetTextareaAttributeCodesCompatibleWithSpellingQueryInterface */
    private $getTextAttributeCodesCompatibleWithSpellingQuery;

    /** @var GetTextareaAttributeCodesCompatibleWithSpellingQueryInterface */
    private $getTextareaAttributeCodesCompatibleWithSpellingQuery;

    public function __construct(
        TextChecker $textChecker,
        BuildProductValuesInterface $buildProductValues,
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        SupportedLocaleChecker $supportedLocaleChecker,
        GetTextAttributeCodesCompatibleWithSpellingQueryInterface $getTextAttributeCodesCompatibleWithSpellingQuery,
        GetTextareaAttributeCodesCompatibleWithSpellingQueryInterface $getTextareaAttributeCodesCompatibleWithSpellingQuery
    ) {
        $this->textChecker = $textChecker;
        $this->buildProductValues = $buildProductValues;
        $this->localesByChannelQuery = $localesByChannelQuery;
        $this->supportedLocaleChecker = $supportedLocaleChecker;
        $this->getTextAttributeCodesCompatibleWithSpellingQuery = $getTextAttributeCodesCompatibleWithSpellingQuery;
        $this->getTextareaAttributeCodesCompatibleWithSpellingQuery = $getTextareaAttributeCodesCompatibleWithSpellingQuery;
    }

    public function evaluate(Write\CriterionEvaluation $criterionEvaluation): CriterionEvaluationResult
    {
        $localesByChannel = $this->localesByChannelQuery->execute();

        $productId = $criterionEvaluation->getProductId();

        $textareaAttributesCodes = $this->getTextareaAttributeCodesCompatibleWithSpellingQuery->byProductId($productId);
        $textareaValuesList = $this->buildProductValues->buildForProductIdAndAttributeCodes($productId, $textareaAttributesCodes);

        $textAttributesCodes = $this->getTextAttributeCodesCompatibleWithSpellingQuery->byProductId($productId);
        $textValuesList = $this->buildProductValues->buildForProductIdAndAttributeCodes($productId, $textAttributesCodes);

        $ratesByChannelAndLocaleTextarea = $this->computeAttributeRates($localesByChannel, $textareaValuesList, self::TEXTAREA_FAULT_WEIGHT);
        $ratesByChannelAndLocaleText = $this->computeAttributeRates($localesByChannel, $textValuesList, self::TEXT_FAULT_WEIGHT);

        $ratesByChannelAndLocale = array_merge_recursive(
            $ratesByChannelAndLocaleText,
            $ratesByChannelAndLocaleTextarea
        );

        $rates = $this->buildCriterionRateCollection($ratesByChannelAndLocale);
        $attributesCodesToImprove = $this->computeAttributeCodesToImprove($ratesByChannelAndLocale);

        return new CriterionEvaluationResult($rates, [
            'attributes' => $attributesCodesToImprove
        ]);
    }

    public function getCode(): CriterionCode
    {
        return new CriterionCode(self::CRITERION_CODE);
    }

    private function computeAttributeRates(array $localesByChannel, array $productValues, int $faultWeight): array
    {
        $evaluatedValues = [];

        foreach ($localesByChannel as $channelCode => $localeCodes) {
            foreach ($localeCodes as $localeCode) {
                $localeCode = new LocaleCode($localeCode);
                if (!$this->supportedLocaleChecker->isSupported($localeCode)) {
                    continue;
                }

                foreach ($productValues as $attributeCode => $productValueByChannelAndLocale) {
                    $productValue = $productValueByChannelAndLocale[$channelCode][$localeCode->__toString()];

                    if ($productValue === null) {
                        continue;
                    }

                    $result = $this->textChecker->check($productValue, $localeCode);

                    $evaluatedValues[$channelCode][$localeCode->__toString()][$attributeCode] = $this->computeProductValueRate($result, $faultWeight);
                }
            }
        }

        return $evaluatedValues;
    }

    private function computeProductValueRate(TextCheckResultCollection $checkTextResult, int $faultWeight): int
    {
        $rate = 100 - count($checkTextResult) * $faultWeight;

        if ($rate < 0) {
            return 0;
        }

        return $rate;
    }

    private function buildCriterionRateCollection(array $ratesByChannelAndLocale): CriterionRateCollection
    {
        $rates = new CriterionRateCollection();
        foreach ($ratesByChannelAndLocale as $channelCode => $ratesByLocale) {
            foreach ($ratesByLocale as $localeCode => $ratesByAttribute) {
                $channelLocaleRate = $this->calculateChannelLocaleRate($ratesByAttribute);
                $rates->addRate(new ChannelCode($channelCode), new LocaleCode($localeCode), new Rate($channelLocaleRate));
            }
        }

        return $rates;
    }

    private function computeAttributeCodesToImprove(array $ratesByChannelAndLocale): array
    {
        $attributesCodesToImprove = [];
        foreach ($ratesByChannelAndLocale as $channelCode => $ratesByLocale) {
            foreach ($ratesByLocale as $localeCode => $ratesByAttribute) {
                $attributesWithNoPerfectRate = array_keys(
                    array_filter($ratesByAttribute, function (int $attributeRate) {
                        return $attributeRate < 100;
                    })
                );
                if (! empty($attributesWithNoPerfectRate)) {
                    $attributesCodesToImprove[$channelCode][$localeCode] = $attributesWithNoPerfectRate;
                }
            }
        }

        return $attributesCodesToImprove;
    }

    private function calculateChannelLocaleRate(array $channelLocaleRates): int
    {
        return (int) round(array_sum($channelLocaleRates) / count($channelLocaleRates), 0, PHP_ROUND_HALF_DOWN);
    }
}
