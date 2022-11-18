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

namespace Akeneo\PerformanceAnalytics\Application\Query;

use Akeneo\PerformanceAnalytics\Domain\AggregationType;
use Akeneo\PerformanceAnalytics\Domain\CategoryCode;
use Akeneo\PerformanceAnalytics\Domain\ChannelCode;
use Akeneo\PerformanceAnalytics\Domain\FamilyCode;
use Akeneo\PerformanceAnalytics\Domain\LocaleCode;
use Akeneo\PerformanceAnalytics\Domain\PeriodType;

final class GetHistoricalTimeToEnrich
{
    public function __construct(
        private \DateTimeImmutable $startDate,
        private \DateTimeImmutable $endDate,
        private PeriodType $aggregationPeriodType,
        private AggregationType $aggregationType,
        private ?ChannelCode $channelFilter = null,
        private ?LocaleCode $localeFilter = null,
        private ?FamilyCode $familyFilter = null,
        private ?CategoryCode $categoryFilter = null
    ) {
    }

    public function startDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    public function endDate(): \DateTimeImmutable
    {
        return $this->endDate;
    }

    public function aggregationPeriodType(): PeriodType
    {
        return $this->aggregationPeriodType;
    }

    public function aggregationType(): AggregationType
    {
        return $this->aggregationType;
    }

    public function channelFilter(): ?ChannelCode
    {
        return $this->channelFilter;
    }

    public function localeFilter(): ?LocaleCode
    {
        return $this->localeFilter;
    }

    public function familyFilter(): ?FamilyCode
    {
        return $this->familyFilter;
    }

    public function categoryFilter(): ?CategoryCode
    {
        return $this->categoryFilter;
    }
}
