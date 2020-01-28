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

namespace Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\EvaluateSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\Text\EvaluateTitleFormatting;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\Textarea\EvaluateUppercaseWords;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Enrichment\EvaluateCompletenessOfNonRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\AxisRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Criterion\LowerCaseWords;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLatestCriteriaEvaluationsByProductIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class GetProductEvaluation
{
    /**
     * @var GetLatestCriteriaEvaluationsByProductIdQueryInterface
     */
    private $getLatestCriteriaEvaluationsByProductIdQuery;

    /** @var GetLocalesByChannelQueryInterface */
    private $getLocalesByChannelQuery;

    public function __construct(
        GetLatestCriteriaEvaluationsByProductIdQueryInterface $getLatestCriteriaEvaluationsByProductIdQuery,
        GetLocalesByChannelQueryInterface $getLocalesByChannelQuery
    ) {
        $this->getLatestCriteriaEvaluationsByProductIdQuery = $getLatestCriteriaEvaluationsByProductIdQuery;
        $this->getLocalesByChannelQuery = $getLocalesByChannelQuery;
    }

    public function get(ProductId $productId): array
    {
        $productEvaluation = $this->getLatestCriteriaEvaluationsByProductIdQuery->execute($productId);
        $channelsLocales = $this->getChannelsLocales();

        $evaluationsArray = iterator_to_array($productEvaluation->getIterator());

        $enrichmentCriteria = $this->filterByAxis($evaluationsArray, 'enrichment');
        $consistencyCriteria = $this->filterByAxis($evaluationsArray, 'consistency');

        return [
            'enrichment' => $this->buildAxisEvaluation($enrichmentCriteria, $channelsLocales),
            'consistency' => $this->buildAxisEvaluation($consistencyCriteria, $channelsLocales),
        ];
    }

    private function filterByAxis(array $evaluations, $filteredAxis)
    {
        return array_filter($evaluations, function (CriterionEvaluation $evaluation) use ($filteredAxis) {
            $currentAxis = $this->getAxis($evaluation->getCriterionCode());

            return $filteredAxis === $currentAxis;
        });
    }

    private function buildAxisEvaluation(array $axisCriteriaEvaluations, ChannelLocaleCollection $channelsLocales): array
    {
        $axisRates = $this->buildAxisRateCollection($axisCriteriaEvaluations);

        $axisEvaluation = [];
        foreach ($channelsLocales as $channelCode => $locales) {
            foreach ($locales as $localeCode) {
                $axisEvaluation[strval($channelCode)][strval($localeCode)] = [
                    'rate' => $this->computeAxisRate($axisRates, $channelCode, $localeCode),
                    'rates' => $this->computeAxisCriteriaRates($axisCriteriaEvaluations, $channelCode, $localeCode),
                    'recommendations' => $this->computeAxisRecommendations($axisCriteriaEvaluations, $channelCode, $localeCode)
                ];
            }
        }

        return $axisEvaluation;
    }

    private function buildAxisRateCollection(array $axisCriteriaEvaluations): AxisRateCollection
    {
        $axisRateCollection = new AxisRateCollection();
        foreach ($axisCriteriaEvaluations as $criterionEvaluation) {
            $axisRateCollection->addCriterionRateCollection($criterionEvaluation->getResult()->getRates());
        }

        return $axisRateCollection;
    }

    private function computeAxisRate(AxisRateCollection $axisRates, ChannelCode $channelCode, LocaleCode $localeCode): ?string
    {
        $axisRate = $axisRates->computeForChannelAndLocale($channelCode, $localeCode);

        return $computedAxisRates[strval($channelCode)][strval($localeCode)]['rate'] = null !== $axisRate ? strval($axisRate) : null;
    }

    private function computeAxisCriteriaRates(array $criteriaEvaluations, ChannelCode $channelCode, LocaleCode $localeCode): array
    {
        $criteriaRates = [];
        /** @var CriterionEvaluation $criteriaEvaluation */
        foreach ($criteriaEvaluations as $criteriaEvaluation) {
            $evaluationResult = $criteriaEvaluation->getResult();
            $rate = null !== $evaluationResult
                ? $evaluationResult->getRates()->getByChannelAndLocale($channelCode, $localeCode)
                : null;

            $criteriaRates[] = [
                'criterion' => strval($criteriaEvaluation->getCriterionCode()),
                'rate' => null !== $rate ? $rate->toInt() : null,
                'letterRate' => null !== $rate ? strval($rate) : null,
            ];
        }

        return $criteriaRates;
    }

    private function computeAxisRecommendations(array $criteriaEvaluations, ChannelCode $channelCode, LocaleCode $localeCode): array
    {
        $recommendations = [];
        /** @var CriterionEvaluation $criteriaEvaluation */
        foreach ($criteriaEvaluations as $criteriaEvaluation) {
            $evaluationResult = $criteriaEvaluation->getResult();
            $evaluationData = null !== $evaluationResult ? $evaluationResult->getData() : [];
            $recommendations[] = [
                'criterion' => strval($criteriaEvaluation->getCriterionCode()),
                'attributes' => $evaluationData['attributes'][strval($channelCode)][strval($localeCode)] ?? []
            ];
        }

        return $recommendations;
    }

    private function getAxis(CriterionCode $code): ?string
    {
        $axes = [
            EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE => 'enrichment',
            EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE => 'enrichment',
            EvaluateUppercaseWords::CRITERION_CODE => 'consistency',
            EvaluateTitleFormatting::CRITERION_CODE => 'consistency',
            EvaluateSpelling::CRITERION_CODE => 'consistency',
            LowerCaseWords::CRITERION_CODE => 'consistency',
        ];

        return $axes[strval($code)] ?? null;
    }

    private function getChannelsLocales(): ChannelLocaleCollection
    {
        return new ChannelLocaleCollection($this->getLocalesByChannelQuery->execute());
    }
}
