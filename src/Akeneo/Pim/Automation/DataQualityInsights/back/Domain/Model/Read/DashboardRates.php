<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\RanksDistribution;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ConsolidationDate;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\TimePeriod;

final class DashboardRates
{
    private const NUMBER_OF_DAYS_TO_RETURN = 7;

    private const NUMBER_OF_WEEKS_TO_RETURN = 4;

    private const NUMBER_OF_MONTHS_TO_RETURN = 6;

    private array $rates;

    private string $channelCode;

    private string $localeCode;

    private string $timePeriod;

    public function __construct(array $rates, ChannelCode $channelCode, LocaleCode $localeCode, TimePeriod $timePeriod)
    {
        $this->rates = $rates;
        $this->channelCode = strval($channelCode);
        $this->localeCode = strval($localeCode);
        $this->timePeriod = strval($timePeriod);
    }

    public function toArray()
    {
        if (! array_key_exists($this->timePeriod, $this->rates)) {
            return [];
        }

        $result = $this->convertRatesByTimePeriod($this->timePeriod);

        $actions = [
            TimePeriod::DAILY => function (array $rates) {
                return $this->ensureRatesContainEnoughDays($rates);
            },
            TimePeriod::WEEKLY => function (array $rates) {
                return $this->ensureRatesContainEnoughWeeks($rates);
            },
            TimePeriod::MONTHLY => function (array $rates) {
                return $this->ensureRatesContainEnoughMonths($rates);
            },
        ];

        return $actions[$this->timePeriod]($result);
    }

    private function convertRatesByTimePeriod(string $timePeriod): array
    {
        $result = [];
        foreach ($this->rates[$timePeriod] as $date => $projectionByDate) {
            if (! isset($projectionByDate[$this->channelCode][$this->localeCode])) {
                $result[$date] = [];
                continue;
            }

            $ranksDistribution = new RanksDistribution($projectionByDate[$this->channelCode][$this->localeCode]);
            $result[$date] = $ranksDistribution->getPercentages();
        }

        return $result;
    }

    private function ensureRatesContainEnoughDays(array $result): array
    {
        $lastDays = [];
        for ($i = self::NUMBER_OF_DAYS_TO_RETURN; $i >= 1; $i--) {
            $dailyTimePeriodDateFormat = (new \DateTimeImmutable())
                ->modify('-' . $i . 'DAY')
                ->format('Y-m-d');

            $lastDays[$dailyTimePeriodDateFormat] = [];
        }

        return $this->fillMissingDates($result, $lastDays);
    }

    private function ensureRatesContainEnoughWeeks(array $result): array
    {
        $weeklyTimePeriodDateFormat = (new ConsolidationDate(new \DateTimeImmutable()))->isLastDayOfWeek() ?
            new \DateTimeImmutable() :
            new \DateTimeImmutable('next sunday');

        $lastWeeks = [];
        for ($i = self::NUMBER_OF_WEEKS_TO_RETURN; $i >= 1; $i--) {
            $newDate = $weeklyTimePeriodDateFormat->modify('-' . $i . 'WEEK');
            $lastWeeks[$newDate->format('Y-m-d')] = [];
        }

        return $this->fillMissingDates($result, $lastWeeks);
    }

    private function ensureRatesContainEnoughMonths(array $result): array
    {
        $monthlyTimePeriodDateFormat = (new ConsolidationDate(new \DateTimeImmutable()))->isLastDayOfMonth() ?
            new \DateTimeImmutable() :
            (new \DateTimeImmutable())->setTimestamp(strtotime(date('Y-m-t')));

        $lastMonths = [];
        for ($i = self::NUMBER_OF_MONTHS_TO_RETURN; $i >= 1; $i--) {
            //the modifier "-x MONTH" does not handle properly the correct number of days in a month (it's just a shortcut for -30 DAY),
            // so I had to use another modifier to navigate through months
            $newDate = $monthlyTimePeriodDateFormat->modify('last day of '.$i.' month ago');
            $lastMonths[$newDate->format('Y-m-d')] = [];
        }

        return $this->fillMissingDates($result, $lastMonths);
    }

    private function fillMissingDates(array $result, array $lastDates): array
    {
        foreach ($lastDates as $date => $value) {
            $lastDates[$date] = $result[$date] ?? [];
        }

        return $lastDates;
    }
}
