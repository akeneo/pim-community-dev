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

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\Textarea;

use Akeneo\Pim\Automation\DataQualityInsights\Application\BuildProductValuesInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\EvaluateCriterionInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\GetProductAttributesCodesInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;

final class EvaluateUppercaseWords implements EvaluateCriterionInterface
{
    public const CRITERION_CODE = 'consistency_textarea_uppercase_words';

    /** @var BuildProductValuesInterface */
    private $buildProductValues;

    /** @var GetProductAttributesCodesInterface */
    private $getProductAttributesCodes;

    /** @var GetLocalesByChannelQueryInterface */
    private $localesByChannelQuery;

    public function __construct(
        BuildProductValuesInterface $buildProductValues,
        GetProductAttributesCodesInterface $getProductAttributesCodes,
        GetLocalesByChannelQueryInterface $localesByChannelQuery
    ) {
        $this->buildProductValues = $buildProductValues;
        $this->getProductAttributesCodes = $getProductAttributesCodes;
        $this->localesByChannelQuery = $localesByChannelQuery;
    }

    public function getCode(): CriterionCode
    {
        return new CriterionCode(self::CRITERION_CODE);
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

    private function computeProductValueRate(?string $productValue): ?int
    {
        if ($productValue === null) {
            return null;
        }

        $productValue = strip_tags($productValue);

        if (trim($productValue) === '') {
            return null;
        }

        if (preg_match('~\p{L}+~u', $productValue) === 0) {
            return 100;
        }

        return mb_strtoupper($productValue) === $productValue ? 0 : 100;
    }

    private function calculateChannelLocaleRate(array $channelLocaleRates): int
    {
        return (int) round(array_sum($channelLocaleRates) / count($channelLocaleRates), 0, PHP_ROUND_HALF_DOWN);
    }
}
