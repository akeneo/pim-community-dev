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

use Akeneo\PerformanceAnalytics\Domain\Period\Month;
use Akeneo\PerformanceAnalytics\Domain\Period\Week;
use Akeneo\PerformanceAnalytics\Domain\PeriodType;
use Akeneo\PerformanceAnalytics\Domain\TimeToEnrich\AverageTimeToEnrich;
use Akeneo\PerformanceAnalytics\Domain\TimeToEnrich\AverageTimeToEnrichCollection;
use Akeneo\PerformanceAnalytics\Domain\TimeToEnrich\AverageTimeToEnrichQuery;
use Akeneo\PerformanceAnalytics\Domain\TimeToEnrich\AverageTimeToEnrichRepository;
use Akeneo\PerformanceAnalytics\Domain\TimeToEnrich\TimeToEnrichValue;

class InMemoryAverageTimeToEnrichRepository implements AverageTimeToEnrichRepository
{
    /**
     * {@inheritdoc}
     */
    public function search(AverageTimeToEnrichQuery $query): AverageTimeToEnrichCollection
    {
        $averageTimeToEnrichList = match ($query->aggregationPeriodType()) {
            PeriodType::WEEK => $this->generateRandomTimeToEnrichListByWeek($query->startDate(), $query->endDate()),
            PeriodType::MONTH => $this->generateRandomTimeToEnrichListByMonth($query->startDate(), $query->endDate()),
            default => throw new \InvalidArgumentException(\sprintf('The \'%s\' period type is not implemented', $query->aggregationPeriodType()->name)),
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
                TimeToEnrichValue::fromHours((float) random_int(10, 100) * 24)
            );
            $currentDate = $currentDate->modify('+1 week');
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
                TimeToEnrichValue::fromHours((float) random_int(10, 100) * 24)
            );
            $currentDate = $currentDate->modify('+1 MONTH');
        } while ($currentDate <= $endDate);

        return $averageTimeToEnrichList;
    }
}
