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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\AxisEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLatestCriteriaEvaluationsByProductIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLatestProductEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

/**
 * Return format:
 *
 * [
 *  axis => [
 *      channel => [
 *          locale => [
 *              'rate' => [
 *                  'value' => float  // axis rate number,
 *                  'rank' => string  // axis rate letter,
 *              ],
 *              'criteria' => [
 *                  [
 *                      'code' => string  // code of the criterion,
 *                      'status' => string // status of the criterion evaluation (done, not_applicable, error)
 *                      'rate => [
 *                          'value' => float  // integer value of the criterion rate,
 *                          'rank' => string  // criterion rate letter,
 *                      ],
 *                      'improvable_attributes' => string[] // list of the code of the attributes to improve
 *                  ]
 *              ],
 *          ]
 *      ]
 *  ]
 * ]
 */
class GetProductEvaluation
{
    /** @var GetLatestCriteriaEvaluationsByProductIdQueryInterface */
    private $getLatestCriteriaEvaluationsByProductIdQuery;

    /** @var GetLocalesByChannelQueryInterface */
    private $getLocalesByChannelQuery;

    public function __construct(
        GetLatestProductEvaluationQueryInterface $getLatestProductEvaluationQuery,
        GetLocalesByChannelQueryInterface $getLocalesByChannelQuery
    ) {
        $this->getLatestCriteriaEvaluationsByProductIdQuery = $getLatestProductEvaluationQuery;
        $this->getLocalesByChannelQuery = $getLocalesByChannelQuery;
    }

    public function get(ProductId $productId): array
    {
        $latestProductEvaluation = $this->getLatestCriteriaEvaluationsByProductIdQuery->execute($productId);
        $channelsLocales = $this->getLocalesByChannelQuery->getChannelLocaleCollection();
        $productEvaluation = [];

        /** @var AxisEvaluation $axisEvaluation */
        foreach ($latestProductEvaluation->getAxesEvaluations() as $axisEvaluation) {
            $productEvaluation[strval($axisEvaluation->getAxisCode())] = $this->formatAxisEvaluation($axisEvaluation, $channelsLocales);
        }

        return $productEvaluation;
    }

    private function formatAxisEvaluation(AxisEvaluation $axisEvaluation, ChannelLocaleCollection $channelsLocales): array
    {
        $formattedAxisEvaluation = [];
        foreach ($channelsLocales as $channelCode => $locales) {
            foreach ($locales as $localeCode) {
                $formattedAxisEvaluation[strval($channelCode)][strval($localeCode)] = [
                    'rate' => $this->formatAxisRate($axisEvaluation, $channelCode, $localeCode),
                    'criteria' => $this->formatAxisCriteria($axisEvaluation, $channelCode, $localeCode),
                ];
            }
        }

        return $formattedAxisEvaluation;
    }

    private function formatAxisRate(AxisEvaluation $axisEvaluation, ChannelCode $channelCode, $localeCode): array
    {
        $axisRate = $axisEvaluation->getRates()->getByChannelAndLocale($channelCode, $localeCode);

        return [
            'value' => null !== $axisRate ? $axisRate->toInt() : null,
            'rank' => null !== $axisRate ? $axisRate->toLetter() : null,
        ];
    }

    private function formatAxisCriteria(AxisEvaluation $axisEvaluation, ChannelCode $channelCode, LocaleCode $localeCode): array
    {
        $criteriaRates = [];
        /** @var CriterionEvaluation $criterionEvaluation */
        foreach ($axisEvaluation->getCriteriaEvaluations() as $criterionEvaluation) {
            $evaluationResult = $criterionEvaluation->getResult();
            $rate = null !== $evaluationResult ? $evaluationResult->getRates()->getByChannelAndLocale($channelCode, $localeCode) : null;
            $attributes = null !== $evaluationResult ? $evaluationResult->getAttributes()->getByChannelAndLocale($channelCode, $localeCode) : [];
            $status = null !== $evaluationResult ? $evaluationResult->getStatus()->get($channelCode, $localeCode) : null;

            $criteriaRates[] = [
                'code' => strval($criterionEvaluation->getCriterionCode()),
                'rate' => [
                    "value" => null !== $rate ? $rate->toInt() : null,
                    "rank" => null !== $rate ? $rate->toLetter() : null,
                ],
                'improvable_attributes' => $attributes ?? [],
                'status' => null !== $status ? strval($status) : CriterionEvaluationResultStatus::IN_PROGRESS,
            ];
        }

        return $criteriaRates;
    }
}
