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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ConsolidationDate;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Periodicity;

final class DashboardRates
{
    private const NUMBER_OF_DAYS_TO_RETURN = 7;

    private const NUMBER_OF_WEEKS_TO_RETURN = 4;

    private const NUMBER_OF_MONTHS_TO_RETURN = 6;

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

        $result = $this->convertRatesByPeriodicity($this->periodicity);

        $actions = [
            Periodicity::DAILY => function(array $rates) {
                return $this->ensureRatesContainsEnoughDays($rates);
            },
            Periodicity::WEEKLY => function(array $rates) {
                return $this->ensureRatesContainsEnoughWeeks($rates);
            },
            Periodicity::MONTHLY => function(array $rates) {
                return $this->ensureRatesContainsEnoughMonths($rates);
            },
        ];

        return $actions[$this->periodicity]($result);
    }

    private function convertRatesByPeriodicity(string $periodicity): array
    {
        $result = [];
        foreach ($this->rates[$periodicity] as $date => $projectionByDate) {
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
            return round($rate / $totalRates * 100, 2);
        }, $ratesNumberByRank);
    }

    private function ensureRatesContainsEnoughDays(array $result): array
    {
        $lastSevenDays = [];
        for ($i = self::NUMBER_OF_DAYS_TO_RETURN; $i >= 1; $i--) {
            $dailyPeriodicityDateFormat = (new \DateTimeImmutable())
                ->modify('-' . $i . 'DAY')
                ->format('Y-m-d');

            $lastSevenDays[$dailyPeriodicityDateFormat] = [];
        }

        return $this->fillMissingDates($result, $lastSevenDays);
    }

    private function ensureRatesContainsEnoughWeeks(array $result): array
    {
        $weeklyPeriodicityDateFormat = (new ConsolidationDate(new \DateTimeImmutable()))->isLastDayOfWeek() ?
            new \DateTimeImmutable() :
            new \DateTimeImmutable('next sunday');

        $lastFourWeeks = [];
        for ($i = self::NUMBER_OF_WEEKS_TO_RETURN; $i >= 1; $i--) {
            $newDate = $weeklyPeriodicityDateFormat->modify('-' . $i . 'WEEK');
            $lastFourWeeks[$newDate->format('Y-m-d')] = [];
        }

        return $this->fillMissingDates($result, $lastFourWeeks);
    }

    private function ensureRatesContainsEnoughMonths(array $result): array
    {
        $lastMonths = [];
        for ($i = self::NUMBER_OF_MONTHS_TO_RETURN; $i >= 1; $i--) {
            $monthlyPeriodicityDateFormat = (new \DateTimeImmutable())
                ->modify('-' . $i . 'MONTH')
                ->format('Y-m');
            $lastMonths[$monthlyPeriodicityDateFormat] = [];
        }

        return $this->fillMissingDates($result, $lastMonths);
    }

    private function fillMissingDates(array $result, array $lastDates): array
    {
        foreach ($result as $axisName => $ranksByPeriod) {
            $ranksByPeriod = array_intersect_key($ranksByPeriod, $lastDates);
            $ranksByPeriod = array_replace($lastDates, $ranksByPeriod);
            $result[$axisName] = $ranksByPeriod;
        }

        return $result;
    }
}
