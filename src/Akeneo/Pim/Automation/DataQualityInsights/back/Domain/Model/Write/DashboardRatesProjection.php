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

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\RanksDistributionCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ConsolidationDate;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\TimePeriod;

final class DashboardRatesProjection
{
    /** @var DashboardProjectionType */
    private $type;

    /** @var DashboardProjectionCode */
    private $code;

    /** @var ConsolidationDate */
    private $consolidationDate;

    /** @var RanksDistributionCollection */
    private $ranksDistributionCollection;

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
