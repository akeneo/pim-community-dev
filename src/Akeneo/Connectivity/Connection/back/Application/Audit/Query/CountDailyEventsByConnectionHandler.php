<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Audit\Query;

use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\SelectConnectionsEventCountByDayQuery;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CountDailyEventsByConnectionHandler
{
    /** @var SelectConnectionsEventCountByDayQuery */
    private $selectConnectionsEventCountByDayQuery;

    public function __construct(SelectConnectionsEventCountByDayQuery $selectConnectionsEventCountByDayQuery)
    {
        $this->selectConnectionsEventCountByDayQuery = $selectConnectionsEventCountByDayQuery;
    }

    public function handle(CountDailyEventsByConnectionQuery $query): array
    {
        return $this
            ->selectConnectionsEventCountByDayQuery
            ->execute($query->eventType(), $query->startDate(), $query->endDate());
    }
}
