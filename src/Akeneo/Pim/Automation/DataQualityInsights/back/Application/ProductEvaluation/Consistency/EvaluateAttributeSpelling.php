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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributeSpellcheckQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\SpellCheckResult;

/**
 * For performance and data size reasons, this criterion is evaluated and its results stored by family for the products (not for product models)
 * So this class doesn't implement "EvaluateCriterionInterface" to be able to evaluate by family as well as product model.
 */
final class EvaluateAttributeSpelling
{
    public const CRITERION_CODE = 'consistency_attribute_spelling';

    public const CRITERION_COEFFICIENT = 1;

    private GetLocalesByChannelQueryInterface $localesByChannelQuery;

    private GetAttributeSpellcheckQueryInterface $getAttributeSpellcheckQuery;

    public function __construct(
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        GetAttributeSpellcheckQueryInterface $getAttributeSpellcheckQuery
    ) {
        $this->localesByChannelQuery = $localesByChannelQuery;
        $this->getAttributeSpellcheckQuery = $getAttributeSpellcheckQuery;
    }

    public function byAttributeCodes(array $attributeCodes): Write\CriterionEvaluationResult
    {
        $localesByChannel = $this->localesByChannelQuery->getChannelLocaleCollection();
        $attributeSpellchecks = $this->getAttributeSpellcheckQuery->getByAttributeCodes($attributeCodes);

        $attributeRatesByLocale = $this->computeAttributeRatesByLocale($localesByChannel, $attributeCodes, $attributeSpellchecks);

        $evaluationResult = new Write\CriterionEvaluationResult();
        foreach ($localesByChannel as $channelCode => $localeCodes) {
            foreach ($localeCodes as $localeCode) {
                $rateByAttributes = $attributeRatesByLocale[strval($localeCode)];
                $status = empty($rateByAttributes) ? CriterionEvaluationResultStatus::notApplicable() : CriterionEvaluationResultStatus::done();
                $evaluationResult->addStatus($channelCode, $localeCode, $status);
                if (! empty($rateByAttributes)) {
                    $evaluationResult
                        ->addRateByAttributes($channelCode, $localeCode, $rateByAttributes)
                        ->addRate($channelCode, $localeCode, $this->calculateLocaleRate($rateByAttributes));
                }
            }
        }

        return $evaluationResult;
    }

    private function computeAttributeRatesByLocale(ChannelLocaleCollection $localesByChannel, array $familyAttributeCodes, array $attributeSpellchecks): array
    {
        $result = [];
        foreach ($localesByChannel as $channelCode => $localeCodes) {
            foreach ($localeCodes as $localeCode) {
                if (array_key_exists(strval($localeCode), $result)) {
                    continue;
                }
                $result[strval($localeCode)] = $this->computeAttributesRates($localeCode, $familyAttributeCodes, $attributeSpellchecks);
            }
        }

        return $result;
    }

    private function computeAttributesRates(LocaleCode $localeCode, array $familyAttributeCodes, array $attributeSpellchecks): array
    {
        $result = [];
        foreach ($familyAttributeCodes as $attributeCode) {
            if (! array_key_exists(strval($attributeCode), $attributeSpellchecks)) {
                continue;
            }
            $attributeSpellcheck = $attributeSpellchecks[strval($attributeCode)]->getResult();

            $localeResult = $attributeSpellcheck->getLocaleResult($localeCode);
            if (! $localeResult instanceof SpellCheckResult) {
                continue;
            }

            $result[strval($attributeCode)] = $localeResult->isToImprove() ? 0 : 100;
        }

        return $result;
    }

    private function calculateLocaleRate(array $localeRates): Rate
    {
        return new Rate((int) round(array_sum($localeRates) / count($localeRates), 0, PHP_ROUND_HALF_DOWN));
    }
}
