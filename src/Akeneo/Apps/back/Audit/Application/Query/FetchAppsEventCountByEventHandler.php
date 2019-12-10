<?php

declare(strict_types=1);

namespace Akeneo\Apps\Audit\Application\Query;

use Akeneo\Apps\Audit\Domain\Persistence\Query\SelectAppsEventCountByDateQuery;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FetchAppsEventCountByEventHandler
{
    /** @var SelectAppsEventCountByDateQuery */
    private $selectAppsEventCountByDateQuery;

    public function __construct(SelectAppsEventCountByDateQuery $selectAppsEventCountByDateQuery)
    {
        $this->selectAppsEventCountByDateQuery = $selectAppsEventCountByDateQuery;
    }

    public function handle(FetchAppsEventCountByEventQuery $query)
    {
        $eventCountByApps = $this
            ->selectAppsEventCountByDateQuery
            ->execute($query->eventType(), $query->startDate(), $query->endDate());

        $eventCount = [];
        foreach ($eventCountByApps as $eventCountByApp) {
            $eventCount[] = $eventCountByApp->normalize();
        }

        return $eventCount;
    }
}
