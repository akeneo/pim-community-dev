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
use Webmozart\Assert\Assert;

final class AverageTimeToEnrichQuery
{
    /**
     * @param ChannelCode[]|null $channelCodesFilter
     * @param LocaleCode[]|null $localeCodesFilter
     * @param FamilyCode[]|null $familyCodesFilter
     * @param CategoryCode[]|null $categoryCodesFilter
     */
    public function __construct(
        private readonly \DateTimeImmutable $startDate,
        private readonly \DateTimeImmutable $endDate,
        private readonly PeriodType $aggregationPeriodType,
        private readonly ?array $channelCodesFilter = null,
        private readonly ?array $localeCodesFilter = null,
        private readonly ?array $familyCodesFilter = null,
        private readonly ?array $categoryCodesFilter = null
    ) {
        if ($this->channelCodesFilter) {
            Assert::allIsInstanceOf($this->channelCodesFilter, ChannelCode::class);
        }

        if ($this->localeCodesFilter) {
            Assert::allIsInstanceOf($this->localeCodesFilter, LocaleCode::class);
        }

        if ($this->familyCodesFilter) {
            Assert::allIsInstanceOf($this->familyCodesFilter, FamilyCode::class);
        }

        if ($this->categoryCodesFilter) {
            Assert::allIsInstanceOf($this->categoryCodesFilter, CategoryCode::class);
        }
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

    /**
     * @return ChannelCode[]|null
     */
    public function channelCodesFilter(): ?array
    {
        return $this->channelCodesFilter;
    }

    /**
     * @return LocaleCode[]|null
     */
    public function localeCodesFilter(): ?array
    {
        return $this->localeCodesFilter;
    }

    /**
     * @return FamilyCode[]|null
     */
    public function familyCodesFilter(): ?array
    {
        return $this->familyCodesFilter;
    }

    /**
     * @return CategoryCode[]|null
     */
    public function categoryCodesFilter(): ?array
    {
        return $this->categoryCodesFilter;
    }
}
