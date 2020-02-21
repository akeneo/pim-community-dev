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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
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

    public function evaluate(Write\CriterionEvaluation $criterionEvaluation): Write\CriterionEvaluationResult
    {
        $localesByChannel = $this->localesByChannelQuery->getChannelLocaleCollection();
        $attributesCodes = $this->getProductAttributesCodes->getTextarea($criterionEvaluation->getProductId());
        $productValues = $this->buildProductValues->buildForProductIdAndAttributeCodes($criterionEvaluation->getProductId(), $attributesCodes);

        $evaluationResult = new Write\CriterionEvaluationResult();
        foreach ($localesByChannel as $channelCode => $localeCodes) {
            foreach ($localeCodes as $localeCode) {
                $this->evaluateChannelLocaleRate($evaluationResult, $channelCode, $localeCode, $productValues);
            }
        }

        return $evaluationResult;
    }

    private function evaluateChannelLocaleRate(Write\CriterionEvaluationResult $evaluationResult, ChannelCode $channelCode, LocaleCode $localeCode, array $productValues): void
    {
        $attributesRates = [];
        foreach ($productValues as $attributeCode => $productValueByChannelAndLocale) {
            $productValue = $productValueByChannelAndLocale[strval($channelCode)][strval($localeCode)] ?? null;
            $rate = $this->computeProductValueRate($productValue);

            if ($rate !== null) {
                $attributesRates[$attributeCode] = $rate;
            }
        }

        if (empty($attributesRates)) {
            $evaluationResult->addStatus($channelCode, $localeCode, CriterionEvaluationResultStatus::notApplicable());
            return;
        }

        $rate = $this->calculateChannelLocaleRate($attributesRates);
        $improvableAttributes = $this->computeImprovableAttributes($attributesRates);

        $evaluationResult
            ->addStatus($channelCode, $localeCode, CriterionEvaluationResultStatus::done())
            ->addRate($channelCode, $localeCode, $rate)
            ->addImprovableAttributes($channelCode, $localeCode, $improvableAttributes);
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
        $channelLocaleRates = array_map(function (Rate $rate) {
            return $rate->toInt();
        }, $channelLocaleRates);

        return new Rate((int) round(array_sum($channelLocaleRates) / count($channelLocaleRates), 0, PHP_ROUND_HALF_DOWN));
    }

    private function computeImprovableAttributes(array $attributesRates): array
    {
        return array_keys(array_filter($attributesRates, function (Rate $attributeRate) {
            return !$attributeRate->isPerfect();
        }));
    }
}
