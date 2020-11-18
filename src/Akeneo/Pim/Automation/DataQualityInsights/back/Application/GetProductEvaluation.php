<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CriteriaEvaluationRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetCriteriaEvaluationsByProductIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetLatestProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Return format:
 *
 * [
 *    channel => [
 *        locale => [
 *            'score' => [
 *                'value' => float  // score number (0-100),
 *                'rank' => string  // score letter,
 *            ],
 *            'criteria' => [
 *                [
 *                    'code' => string  // code of the criterion,
 *                    'status' => string // status of the criterion evaluation (done, not_applicable, error)
 *                    'rate => [
 *                        'value' => float  // integer value of the criterion rate,
 *                        'rank' => string  // criterion rate letter,
 *                    ],
 *                    'improvable_attributes' => string[] // list of the code of the attributes to improve
 *                ]
 *            ],
 *        ]
 *    ]
 * ]
 */
class GetProductEvaluation
{
    private GetCriteriaEvaluationsByProductIdQueryInterface $getCriteriaEvaluationsByProductIdQuery;

    private GetLatestProductScoresQueryInterface $getLatestProductScoresQuery;

    private GetLocalesByChannelQueryInterface $getLocalesByChannelQuery;

    private CriteriaEvaluationRegistry $criteriaEvaluationRegistry;

    public function __construct(
        GetCriteriaEvaluationsByProductIdQueryInterface $getCriteriaEvaluationsByProductIdQuery,
        GetLatestProductScoresQueryInterface $getLatestProductScoresQuery,
        GetLocalesByChannelQueryInterface $getLocalesByChannelQuery,
        CriteriaEvaluationRegistry $criteriaEvaluationRegistry
    ) {
        $this->getCriteriaEvaluationsByProductIdQuery = $getCriteriaEvaluationsByProductIdQuery;
        $this->getLatestProductScoresQuery = $getLatestProductScoresQuery;
        $this->getLocalesByChannelQuery = $getLocalesByChannelQuery;
        $this->criteriaEvaluationRegistry = $criteriaEvaluationRegistry;
    }

    public function get(ProductId $productId): array
    {
        $productScores = $this->getLatestProductScoresQuery->byProductId($productId);
        $criteriaEvaluations = $this->getCriteriaEvaluationsByProductIdQuery->execute($productId);
        $channelsLocales = $this->getLocalesByChannelQuery->getChannelLocaleCollection();

        $formattedProductEvaluation = [];

        foreach ($channelsLocales as $channelCode => $locales) {
            foreach ($locales as $localeCode) {
                $formattedProductEvaluation[strval($channelCode)][strval($localeCode)] = [
                    'score' => $this->formatScore($productScores, $channelCode, $localeCode),
                    'criteria' => $this->formatCriteriaEvaluations($criteriaEvaluations, $channelCode, $localeCode),
                ];
            }
        }

        return $formattedProductEvaluation;
    }

    private function formatScore(ChannelLocaleRateCollection $productScores, ChannelCode $channelCode, $localeCode): array
    {
        $score = $productScores->getByChannelAndLocale($channelCode, $localeCode);

        return [
            'value' => null !== $score ? $score->toInt() : null,
            'rank' => null !== $score ? $score->toLetter() : null,
        ];
    }

    private function formatCriteriaEvaluations(Read\CriterionEvaluationCollection $criteriaEvaluations, ChannelCode $channelCode, LocaleCode $localeCode): array
    {
        $criteriaRates = [];

        foreach ($this->criteriaEvaluationRegistry->getCriterionCodes() as $criterionCode) {
            $criterionEvaluation = $criteriaEvaluations->get($criterionCode);
            $criteriaRates[] = $this->formatCriterionEvaluation(
                $criterionCode,
                $criterionEvaluation !== null ? $criterionEvaluation->getResult() : null,
                $channelCode,
                $localeCode
            );
        }

        return $criteriaRates;
    }

    private function formatCriterionEvaluation(CriterionCode $criterionCode, ?Read\CriterionEvaluationResult $evaluationResult, ChannelCode $channelCode, LocaleCode $localeCode): array
    {
        $rate = null !== $evaluationResult ? $evaluationResult->getRates()->getByChannelAndLocale($channelCode, $localeCode) : null;
        $attributes = null !== $evaluationResult ? $evaluationResult->getAttributes()->getByChannelAndLocale($channelCode, $localeCode) : [];
        $status = null !== $evaluationResult ? $evaluationResult->getStatus()->get($channelCode, $localeCode) : null;

        return [
            'code' => strval($criterionCode),
            'rate' => [
                'value' => null !== $rate ? $rate->toInt() : null,
                'rank' => null !== $rate ? $rate->toLetter() : null,
            ],
            'improvable_attributes' => $attributes ?? [],
            'status' => null !== $status ? strval($status) : CriterionEvaluationResultStatus::IN_PROGRESS,
        ];
    }
}
