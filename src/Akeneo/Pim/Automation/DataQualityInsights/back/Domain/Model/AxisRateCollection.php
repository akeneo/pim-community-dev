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

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rank;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;

final class AxisRateCollection
{
    private $rates = [];

    public function addCriterionRateCollection(CriterionRateCollection $rateCollection): self
    {
        foreach ($rateCollection as $channelCode => $localeCodes) {
            foreach ($localeCodes as $localeCode => $rate) {
                $this->rates[$channelCode][$localeCode][] = $rate;
            }
        }

        return $this;
    }

    public function toArrayString(): array
    {
        return array_map(function ($channelRates) {
            return array_map(function ($localeRates) {
                $averageRate = $this->computeAverageRate($localeRates);
                return $this->convertRateToString($averageRate);
            }, $channelRates);
        }, $this->rates);
    }

    public function formatForConsolidation(): array
    {
        return array_map(function ($channelRates) {
            return array_map(function ($localeRates) {
                $averageRate = $this->computeAverageRate($localeRates);
                return [
                    'rank' => $this->convertRateToRank($averageRate),
                    'value' => $averageRate->toInt(),
                ];
            }, $channelRates);
        }, $this->rates);
    }

    public function computeForChannelAndLocale(ChannelCode $channelCode, LocaleCode $localeCode): ?Rate
    {
        $rates = $this->rates[strval($channelCode)][strval($localeCode)] ?? null;

        return null !== $rates ? $this->computeAverageRate($rates) : null;
    }

    private function convertRateToString(Rate $rate): string
    {
        $rank = Rank::fromRate($rate);

        return $rank->toLetter();
    }

    private function convertRateToRank(Rate $rate): int
    {
        $rank = Rank::fromRate($rate);

        return $rank->toInt();
    }

    private function computeAverageRate($channelLocaleRates): Rate
    {
        $channelLocaleRates = array_map(function (Rate $rate) {
            return $rate->toInt();
        }, $channelLocaleRates);

        $average = array_sum($channelLocaleRates) / count($channelLocaleRates);

        return new Rate($this->roundRate($average));
    }

    private function roundRate(float $value): int
    {
        return (int) round($value, 0, PHP_ROUND_HALF_DOWN);
    }
}
