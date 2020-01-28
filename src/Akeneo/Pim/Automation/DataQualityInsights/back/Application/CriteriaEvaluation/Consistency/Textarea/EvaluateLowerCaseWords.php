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

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\Textarea;

use Akeneo\Pim\Automation\DataQualityInsights\Application\BuildProductValuesInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\EvaluateCriterionInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\GetProductAttributesCodesInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Criterion\LowerCaseWords;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;

final class EvaluateLowerCaseWords implements EvaluateCriterionInterface
{
    /** @var BuildProductValuesInterface */
    private $buildProductValues;

    /** @var GetProductAttributesCodesInterface */
    private $getProductAttributesCodes;

    /** @var GetLocalesByChannelQueryInterface */
    private $localesByChannelQuery;

    /** @var LowerCaseWords */
    private $criterion;

    public function __construct(
        BuildProductValuesInterface $buildProductValues,
        GetProductAttributesCodesInterface $getProductAttributesCodes,
        GetLocalesByChannelQueryInterface $localesByChannelQuery
    ) {
        $this->buildProductValues = $buildProductValues;
        $this->getProductAttributesCodes = $getProductAttributesCodes;
        $this->localesByChannelQuery = $localesByChannelQuery;
        $this->criterion = new LowerCaseWords();
    }

    public function getCode(): CriterionCode
    {
        return $this->criterion->getCode();
    }

    public function evaluate(Write\CriterionEvaluation $criterionEvaluation): CriterionEvaluationResult
    {
        $localesByChannel = $this->localesByChannelQuery->execute();
        $attributesCodes = $this->getProductAttributesCodes->getTextarea($criterionEvaluation->getProductId());

        $productValues = $this->buildProductValues->buildForProductIdAndAttributeCodes($criterionEvaluation->getProductId(), $attributesCodes);

        $ratesByChannelAndLocale = $this->computeAttributeRates($localesByChannel, $productValues);
        $rates = $this->buildCriterionRateCollection($ratesByChannelAndLocale);
        $attributesCodesToImprove = $this->computeAttributeCodesToImprove($ratesByChannelAndLocale);

        return new CriterionEvaluationResult($rates, [
            'attributes' => $attributesCodesToImprove
        ]);
    }

    private function computeAttributeRates(array $localesByChannel, array $productValues): array
    {
        $ratesByChannelAndLocale = [];

        foreach ($localesByChannel as $channelCode => $localeCodes) {
            foreach ($localeCodes as $localeCode) {
                foreach ($productValues as $attributeCode => $productValueByChannelAndLocale) {
                    $productValue = $productValueByChannelAndLocale[$channelCode][$localeCode];
                    $rate = $this->computeProductValueRate($productValue);
                    if ($rate === null) {
                        continue;
                    }
                    $ratesByChannelAndLocale[$channelCode][$localeCode][$attributeCode] = $rate;
                }
            }
        }

        return $ratesByChannelAndLocale;
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
                    array_filter($ratesByAttribute, function (Rate $attributeRate) {
                        return !$attributeRate->isPerfect();
                    })
                );
                if (! empty($attributesWithNoPerfectRate)) {
                    $attributesCodesToImprove[$channelCode][$localeCode] = $attributesWithNoPerfectRate;
                }
            }
        }

        return $attributesCodesToImprove;
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

    private function calculateChannelLocaleRate(array $channelLocaleRates): int
    {
        $channelLocaleRates = array_map(function (Rate $rate) {
            return $rate->toInt();
        }, $channelLocaleRates);

        return (int) round(array_sum($channelLocaleRates) / count($channelLocaleRates), 0, PHP_ROUND_HALF_DOWN);
    }
}
