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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rank;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;

final class AxisRateCollection
{
    private $rates = [];

    public function addCriterionRateCollection(CriterionRateCollection $rateCollection): self
    {
        foreach ($rateCollection as $channelCode => $localeCodes) {
            foreach ($localeCodes as $localeCode => $rate) {
                $this->rates[$channelCode][$localeCode][] = $rate->toInt();
            }
        }

        return $this;
    }

    public function toArrayString(): array
    {
        return array_map(function ($channelRates) {
            return array_map(function ($localeRates) {
                $average = $this->computeChannelLocaleAverage($localeRates);
                return $this->convertRateToString($average);
            }, $channelRates);
        }, $this->rates);
    }

    public function formatForConsolidation(): array
    {
        return array_map(function ($channelRates) {
            return array_map(function ($localeRates) {
                $average = $this->computeChannelLocaleAverage($localeRates);
                return [
                    'rank' => $this->convertRateToRank($average),
                    'value' => $average,
                ];
            }, $channelRates);
        }, $this->rates);
    }

    private function convertRateToString(int $value): string
    {
        $rank = Rank::fromRate(new Rate($value));

        return $rank->toLetter();
    }

    private function convertRateToRank(int $value): int
    {
        $rank = Rank::fromRate(new Rate($value));

        return $rank->toInt();
    }

    private function computeChannelLocaleAverage($channelLocaleRates): int
    {
        $average = array_sum($channelLocaleRates) / count($channelLocaleRates);

        return $this->roundRate($average);
    }

    private function roundRate(float $value): int
    {
        return (int) round($value, 0, PHP_ROUND_HALF_DOWN);
    }
}
