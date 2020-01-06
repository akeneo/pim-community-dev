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

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Periodicity;

final class DashboardRates
{
    /** @var array */
    private $rates;

    /** @var string */
    private $channelCode;

    /** @var string */
    private $localeCode;

    /** @var string */
    private $periodicity;

    public function __construct(array $rates, ChannelCode $channelCode, LocaleCode $localeCode, Periodicity $periodicity)
    {
        $this->rates = $rates;
        $this->channelCode = strval($channelCode);
        $this->localeCode = strval($localeCode);
        $this->periodicity = strval($periodicity);
    }

    public function toArray()
    {
        if (! array_key_exists($this->periodicity, $this->rates)) {
            return [];
        }

        $result = [];
        foreach ($this->rates['daily'] as $date => $projectionByDate) {
            foreach ($projectionByDate as $axisName => $axisProjection) {
                if (! isset($axisProjection[$this->channelCode][$this->localeCode])) {
                    $result[$axisName][$date] = [];
                    continue;
                }

                $ratesNumberByRank = $this->computeRatesNumberByRank($axisProjection);
                $ratesRepartition = $this->computeRatesRepartition($ratesNumberByRank);

                $result[$axisName][$date] = $ratesRepartition;
            }
        }

        return $result;
    }

    private function computeRatesNumberByRank($axisProjection): array
    {
        $ranks = [
            "rank_1" => 0,
            "rank_2" => 0,
            "rank_3" => 0,
            "rank_4" => 0,
            "rank_5" => 0,
        ];

        return array_replace($ranks, $axisProjection[$this->channelCode][$this->localeCode]);
    }

    private function computeRatesRepartition(array $ratesNumberByRank): array
    {
        $totalRates = array_sum($ratesNumberByRank);

        return array_map(function ($rate) use ($totalRates) {
            return round($rate / $totalRates * 100);
        }, $ratesNumberByRank);
    }
}
