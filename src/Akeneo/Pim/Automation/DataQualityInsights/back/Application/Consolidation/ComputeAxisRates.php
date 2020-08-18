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

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Axis;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;

class ComputeAxisRates
{
    /** @var GetLocalesByChannelQueryInterface */
    private $getLocalesByChannelQuery;

    /** @var ChannelLocaleCollection */
    private $channelsLocales;

    public function __construct(GetLocalesByChannelQueryInterface $getLocalesByChannelQuery)
    {
        $this->getLocalesByChannelQuery = $getLocalesByChannelQuery;
    }

    public function compute(Axis $axis, Read\CriterionEvaluationCollection $criteriaEvaluations): ChannelLocaleRateCollection
    {
        $axisRates = new ChannelLocaleRateCollection();

        foreach ($this->getChannelsLocales() as $channelCode => $locales) {
            foreach ($locales as $localeCode) {
                $rate = $this->computeAxisRate($axis, $criteriaEvaluations, $channelCode, $localeCode);
                if (null !== $rate) {
                    $axisRates->addRate($channelCode, $localeCode, $rate);
                }
            }
        }

        return $axisRates;
    }

    private function computeAxisRate(Axis $axis, Read\CriterionEvaluationCollection $criteriaEvaluations, ChannelCode $channelCode, LocaleCode $localeCode): ?Rate
    {
        $criteriaEvaluations = $criteriaEvaluations->filterByAxis($axis);
        $criteriaRates = [];
        $totalCoefficient = 0;

        foreach ($axis->getCriteriaCodes() as $criterionCode) {
            $criterionRates = $criteriaEvaluations->getCriterionRates($criterionCode);
            $criterionRate = null !== $criterionRates ? $criterionRates->getByChannelAndLocale($channelCode, $localeCode) : null;
            if (null !== $criterionRate) {
                $coefficient = $axis->getCriterionCoefficient($criterionCode);
                $totalCoefficient += $coefficient;
                $criteriaRates[] = $criterionRate->toInt() * $coefficient;
            }
        }

        if (empty($criteriaRates) || $totalCoefficient === 0) {
            return null;
        }

        $axisRate = round(array_sum($criteriaRates) / $totalCoefficient, 0, PHP_ROUND_HALF_DOWN);

        return new Rate(intval($axisRate));
    }

    private function getChannelsLocales(): ChannelLocaleCollection
    {
        if (null === $this->channelsLocales) {
            $this->channelsLocales = $this->getLocalesByChannelQuery->getChannelLocaleCollection();
        }

        return $this->channelsLocales;
    }
}
