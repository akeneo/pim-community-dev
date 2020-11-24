<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\RanksDistributionCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ConsolidationDate;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\TimePeriod;

final class DashboardRatesProjection
{
    private DashboardProjectionType $type;

    private DashboardProjectionCode $code;

    private ConsolidationDate $consolidationDate;

    private RanksDistributionCollection $ranksDistributionCollection;

    public function __construct(
        DashboardProjectionType $type,
        DashboardProjectionCode $code,
        ConsolidationDate $consolidationDate,
        RanksDistributionCollection $ranksDistributionCollection
    ) {
        $this->type = $type;
        $this->code = $code;
        $this->consolidationDate = $consolidationDate;
        $this->ranksDistributionCollection = $ranksDistributionCollection;
    }

    public function getType(): DashboardProjectionType
    {
        return $this->type;
    }

    public function getCode(): DashboardProjectionCode
    {
        return $this->code;
    }

    public function getConsolidationDate(): ConsolidationDate
    {
        return $this->consolidationDate;
    }

    public function getRanksDistributionsPerTimePeriod(): array
    {
        $day = $this->consolidationDate->format();
        $ranksDistribution = $this->ranksDistributionCollection->toArray();

        $rates[TimePeriod::DAILY][$day] = $ranksDistribution;

        if ($this->consolidationDate->isLastDayOfWeek()) {
            $rates[TimePeriod::WEEKLY][$day] = $ranksDistribution;
        }

        if ($this->consolidationDate->isLastDayOfMonth()) {
            $rates[TimePeriod::MONTHLY][$day] = $ranksDistribution;
        }

        if ($this->consolidationDate->isLastDayOfYear()) {
            $rates[TimePeriod::YEARLY][$day] = $ranksDistribution;
        }

        return $rates;
    }

    public function getAverageRanks(): array
    {
        return $this->ranksDistributionCollection->getAverageRanks();
    }
}
