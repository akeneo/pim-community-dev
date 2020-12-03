<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CriteriaEvaluationRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ComputeProductScores
{
    private GetLocalesByChannelQueryInterface $getLocalesByChannelQuery;

    private CriteriaEvaluationRegistry $criteriaEvaluationRegistry;

    public function __construct(
        GetLocalesByChannelQueryInterface $getLocalesByChannelQuery,
        CriteriaEvaluationRegistry $criteriaEvaluationRegistry
    ) {
        $this->getLocalesByChannelQuery = $getLocalesByChannelQuery;
        $this->criteriaEvaluationRegistry = $criteriaEvaluationRegistry;
    }

    public function fromCriteriaEvaluations(Read\CriterionEvaluationCollection $criteriaEvaluations): ChannelLocaleRateCollection
    {
        $scores = new ChannelLocaleRateCollection();

        foreach ($this->getLocalesByChannelQuery->getChannelLocaleCollection() as $channelCode => $locales) {
            foreach ($locales as $localeCode) {
                $score = $this->computeChannelLocaleScore($criteriaEvaluations, $channelCode, $localeCode);
                if (null !== $score) {
                    $scores->addRate($channelCode, $localeCode, $score);
                }
            }
        }

        return $scores;
    }

    private function computeChannelLocaleScore(CriterionEvaluationCollection $criteriaEvaluations, ChannelCode $channelCode, LocaleCode $localeCode): ?Rate
    {
        $criteriaRates = [];
        $totalCoefficient = 0;

        foreach ($this->criteriaEvaluationRegistry->getCriterionCodes() as $criterionCode) {
            $criterionRates = $criteriaEvaluations->getCriterionRates($criterionCode);
            $criterionRate = null !== $criterionRates ? $criterionRates->getByChannelAndLocale($channelCode, $localeCode) : null;
            if (null !== $criterionRate) {
                $coefficient = $this->criteriaEvaluationRegistry->getCriterionCoefficient($criterionCode);
                $totalCoefficient += $coefficient;
                $criteriaRates[] = $criterionRate->toInt() * $coefficient;
            }
        }

        if (empty($criteriaRates) || $totalCoefficient === 0) {
            return null;
        }

        $score = round(array_sum($criteriaRates) / $totalCoefficient, 0, PHP_ROUND_HALF_DOWN);

        return new Rate(intval($score));
    }
}
