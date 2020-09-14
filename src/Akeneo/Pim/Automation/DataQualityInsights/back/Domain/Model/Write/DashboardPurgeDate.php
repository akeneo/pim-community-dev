<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ConsolidationDate;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\TimePeriod;

class DashboardPurgeDate
{
    /** @var TimePeriod */
    private $period;

    /** @var ConsolidationDate */
    private $date;

    public function __construct(TimePeriod $period, ConsolidationDate $date)
    {
        $this->period = $period;
        $this->date = $date;
    }

    public function getPeriod(): TimePeriod
    {
        return $this->period;
    }

    public function getDate(): ConsolidationDate
    {
        return $this->date;
    }
}
