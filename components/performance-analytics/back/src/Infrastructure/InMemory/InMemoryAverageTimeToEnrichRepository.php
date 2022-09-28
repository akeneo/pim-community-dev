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

use Akeneo\PerformanceAnalytics\Domain\CategoryCode;
use Akeneo\PerformanceAnalytics\Domain\ChannelCode;
use Akeneo\PerformanceAnalytics\Domain\FamilyCode;
use Akeneo\PerformanceAnalytics\Domain\LocaleCode;
use Akeneo\PerformanceAnalytics\Domain\Period\Week;
use Akeneo\PerformanceAnalytics\Domain\PeriodType;
use Akeneo\PerformanceAnalytics\Domain\TimeToEnrich\AverageTimeToEnrich;
use Akeneo\PerformanceAnalytics\Domain\TimeToEnrich\AverageTimeToEnrichCollection;
use Akeneo\PerformanceAnalytics\Domain\TimeToEnrich\AverageTimeToEnrichRepository;
use Akeneo\PerformanceAnalytics\Domain\TimeToEnrich\TimeToEnrichValue;

class InMemoryAverageTimeToEnrichRepository implements AverageTimeToEnrichRepository
{
    public function search(
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        PeriodType $aggregationPeriodType,
        ?ChannelCode $channelFilter = null,
        ?LocaleCode $localeFilter = null,
        ?FamilyCode $familyFilter = null,
        ?CategoryCode $categoryFilter = null
    ): AverageTimeToEnrichCollection {
        $averageTimeToEnrichList = [];
        $currentDate = $startDate;

        do {
            $averageTimeToEnrichList[] = AverageTimeToEnrich::fromPeriodAndTimeToEnrichValue(
                Week::fromDate($currentDate),
                TimeToEnrichValue::fromValue((float) random_int(10, 100))
            );
            $currentDate = $currentDate->modify('+1 week');
        } while ($currentDate <= $endDate);

        return AverageTimeToEnrichCollection::fromList($averageTimeToEnrichList);
    }
}
