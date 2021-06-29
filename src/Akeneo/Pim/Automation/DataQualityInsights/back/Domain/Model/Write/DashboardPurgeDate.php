<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ConsolidationDate;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\TimePeriod;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
