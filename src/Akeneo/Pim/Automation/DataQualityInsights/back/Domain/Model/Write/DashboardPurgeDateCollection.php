<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ConsolidationDate;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\TimePeriod;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DashboardPurgeDateCollection implements \IteratorAggregate
{
    /** @var array */
    private $purgeDates = [];

    public function add(TimePeriod $period, ConsolidationDate $date): self
    {
        $this->purgeDates[] = new DashboardPurgeDate($period, $date);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->purgeDates);
    }
}
