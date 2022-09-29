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

namespace Akeneo\PerformanceAnalytics\Domain\TimeToEnrich;

use Akeneo\PerformanceAnalytics\Domain\CategoryCode;
use Akeneo\PerformanceAnalytics\Domain\ChannelCode;
use Akeneo\PerformanceAnalytics\Domain\FamilyCode;
use Akeneo\PerformanceAnalytics\Domain\LocaleCode;
use Akeneo\PerformanceAnalytics\Domain\PeriodType;

interface AverageTimeToEnrichRepository
{
    /**
     * This method is created for historical TTE use case. To handle other TTE use cases,
     * maybe we can refactor it or create a new search method...
     * See later what is the best solution.
     */
    public function search(
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        PeriodType $aggregationPeriodType,
        ?ChannelCode $channelFilter = null,
        ?LocaleCode $localeFilter = null,
        ?FamilyCode $familyFilter = null,
        ?CategoryCode $categoryFilter = null
    ): AverageTimeToEnrichCollection;
}
