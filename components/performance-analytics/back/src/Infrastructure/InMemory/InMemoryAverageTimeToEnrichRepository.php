<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\PerformanceAnalytics\Infrastructure\InMemory;

use Akeneo\PerformanceAnalytics\Domain\AggregationType;
use Akeneo\PerformanceAnalytics\Domain\Period\Day;
use Akeneo\PerformanceAnalytics\Domain\Period\Month;
use Akeneo\PerformanceAnalytics\Domain\Period\Week;
use Akeneo\PerformanceAnalytics\Domain\Period\Year;
use Akeneo\PerformanceAnalytics\Domain\PeriodType;
use Akeneo\PerformanceAnalytics\Domain\TimeToEnrich\AverageTimeToEnrich;
use Akeneo\PerformanceAnalytics\Domain\TimeToEnrich\AverageTimeToEnrichCollection;
use Akeneo\PerformanceAnalytics\Domain\TimeToEnrich\AverageTimeToEnrichRepository;
use Akeneo\PerformanceAnalytics\Domain\TimeToEnrich\TimeToEnrichValue;

class InMemoryAverageTimeToEnrichRepository implements AverageTimeToEnrichRepository
{
    /**
     * {@inheritdoc}
     */
    public function search(
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        PeriodType $aggregationPeriodType,
        AggregationType $aggregationType,
        ?array $channelCodesFilter = null,
        ?array $localeCodesFilter = null,
        ?array $familyCodesFilter = null,
        ?array $categoryCodesFilter = null
    ): AverageTimeToEnrichCollection {
        $averageTimeToEnrichList = match ($aggregationPeriodType) {
            PeriodType::DAY => $this->generateRandomTimeToEnrichListByDay($startDate, $endDate),
            PeriodType::WEEK => $this->generateRandomTimeToEnrichListByWeek($startDate, $endDate),
            PeriodType::MONTH => $this->generateRandomTimeToEnrichListByMonth($startDate, $endDate),
            PeriodType::YEAR => $this->generateRandomTimeToEnrichListByYear($startDate, $endDate),
            default => throw new \InvalidArgumentException(\sprintf('The \'%s\' period type is not implemented', $aggregationPeriodType->name)),
        };

        return AverageTimeToEnrichCollection::fromList($averageTimeToEnrichList);
    }

    /**
     * @return array<AverageTimeToEnrich>
     */
    private function generateRandomTimeToEnrichListByWeek(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        $currentDate = $startDate;
        $averageTimeToEnrichList = [];

        do {
            $averageTimeToEnrichList[] = AverageTimeToEnrich::fromPeriodAndTimeToEnrichValue(
                Week::fromDate($currentDate),
                TimeToEnrichValue::fromValue((float) random_int(10, 100))
            );
            $currentDate = $currentDate->modify('+1 week');
        } while ($currentDate <= $endDate);

        return $averageTimeToEnrichList;
    }

    /**
     * @return array<AverageTimeToEnrich>
     */
    private function generateRandomTimeToEnrichListByDay(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        $currentDate = $startDate;
        $averageTimeToEnrichList = [];

        do {
            $averageTimeToEnrichList[] = AverageTimeToEnrich::fromPeriodAndTimeToEnrichValue(
                Day::fromDate($currentDate),
                TimeToEnrichValue::fromValue((float) random_int(10, 100))
            );
            $currentDate = $currentDate->modify('+1 DAY');
        } while ($currentDate <= $endDate);

        return $averageTimeToEnrichList;
    }

    /**
     * @return array<AverageTimeToEnrich>
     */
    private function generateRandomTimeToEnrichListByMonth(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        $currentDate = $startDate;
        $averageTimeToEnrichList = [];

        do {
            $averageTimeToEnrichList[] = AverageTimeToEnrich::fromPeriodAndTimeToEnrichValue(
                Month::fromDate($currentDate),
                TimeToEnrichValue::fromValue((float) random_int(10, 100))
            );
            $currentDate = $currentDate->modify('+1 MONTH');
        } while ($currentDate <= $endDate);

        return $averageTimeToEnrichList;
    }

    /**
     * @return array<AverageTimeToEnrich>
     */
    private function generateRandomTimeToEnrichListByYear(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        $currentDate = $startDate;
        $averageTimeToEnrichList = [];

        do {
            $averageTimeToEnrichList[] = AverageTimeToEnrich::fromPeriodAndTimeToEnrichValue(
                Year::fromDate($currentDate),
                TimeToEnrichValue::fromValue((float) random_int(10, 100))
            );
            $currentDate = $currentDate->modify('+1 YEAR');
        } while ($currentDate <= $endDate);

        return $averageTimeToEnrichList;
    }
}
