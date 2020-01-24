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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\RanksDistribution;
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
        $result = array_merge(['enrichment' => [], 'consistency' => []], $result);

        $actions = [
            Periodicity::DAILY => function (array $rates) {
                return $this->ensureRatesContainEnoughDays($rates);
            },
            Periodicity::WEEKLY => function (array $rates) {
                return $this->ensureRatesContainEnoughWeeks($rates);
            },
            Periodicity::MONTHLY => function (array $rates) {
                return $this->ensureRatesContainEnoughMonths($rates);
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

                $ranksDistribution = new RanksDistribution($axisProjection[$this->channelCode][$this->localeCode]);
                $result[$axisName][$date] = $ranksDistribution->getPercentages();
            }
        }

        return $result;
    }

    private function ensureRatesContainEnoughDays(array $result): array
    {
        $lastDays = [];
        for ($i = self::NUMBER_OF_DAYS_TO_RETURN; $i >= 1; $i--) {
            $dailyPeriodicityDateFormat = (new \DateTimeImmutable())
                ->modify('-' . $i . 'DAY')
                ->format('Y-m-d');

            $lastDays[$dailyPeriodicityDateFormat] = [];
        }

        return $this->fillMissingDates($result, $lastDays);
    }

    private function ensureRatesContainEnoughWeeks(array $result): array
    {
        $weeklyPeriodicityDateFormat = (new ConsolidationDate(new \DateTimeImmutable()))->isLastDayOfWeek() ?
            new \DateTimeImmutable() :
            new \DateTimeImmutable('next sunday');

        $lastWeeks = [];
        for ($i = self::NUMBER_OF_WEEKS_TO_RETURN; $i >= 1; $i--) {
            $newDate = $weeklyPeriodicityDateFormat->modify('-' . $i . 'WEEK');
            $lastWeeks[$newDate->format('Y-m-d')] = [];
        }

        return $this->fillMissingDates($result, $lastWeeks);
    }

    private function ensureRatesContainEnoughMonths(array $result): array
    {
        $monthlyPeriodicityDateFormat = (new ConsolidationDate(new \DateTimeImmutable()))->isLastDayOfMonth() ?
            new \DateTimeImmutable() :
            (new \DateTimeImmutable())->setTimestamp(strtotime(date('Y-m-t')));

        $lastMonths = [];
        for ($i = self::NUMBER_OF_MONTHS_TO_RETURN; $i >= 1; $i--) {
            //the modifier "-x MONTH" does not handle properly the correct number of days in a month (it's just a shortcut for -30 DAY),
            // so I had to use another modifier to navigate through months
            $newDate = $monthlyPeriodicityDateFormat->modify('last day of '.$i.' month ago');
            $lastMonths[$newDate->format('Y-m-d')] = [];
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
