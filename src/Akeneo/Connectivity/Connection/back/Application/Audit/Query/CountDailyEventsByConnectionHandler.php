<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Audit\Query;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\PeriodEventCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\SelectPeriodEventCountsQuery;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CountDailyEventsByConnectionHandler
{
    /** @var SelectPeriodEventCountsQuery */
    private $selectPeriodEventCountsQuery;

    public function __construct(SelectPeriodEventCountsQuery $selectPeriodEventCountsQuery)
    {
        $this->selectPeriodEventCountsQuery = $selectPeriodEventCountsQuery;
    }

    /**
     * @return PeriodEventCount[]
     */
    public function handle(CountDailyEventsByConnectionQuery $query): array
    {
        $periodEventCounts = $this
            ->selectPeriodEventCountsQuery
            ->execute($query->eventType(), $query->fromDateTime(), $query->upToDateTime());

        return $periodEventCounts;
    }
}
