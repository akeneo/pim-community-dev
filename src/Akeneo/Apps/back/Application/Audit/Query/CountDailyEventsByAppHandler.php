<?php

declare(strict_types=1);

namespace Akeneo\Apps\Application\Audit\Query;

use Akeneo\Apps\Domain\Audit\Persistence\Query\SelectAppsEventCountByDayQuery;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CountDailyEventsByAppHandler
{
    /** @var SelectAppsEventCountByDayQuery */
    private $selectAppsEventCountByDayQuery;

    public function __construct(SelectAppsEventCountByDayQuery $selectAppsEventCountByDayQuery)
    {
        $this->selectAppsEventCountByDayQuery = $selectAppsEventCountByDayQuery;
    }

    public function handle(CountDailyEventsByAppQuery $query): array
    {
        $appEventCounts = $this
            ->selectAppsEventCountByDayQuery
            ->execute($query->eventType(), $query->startDate(), $query->endDate());

        return $eventCountByApps;
    }
}
