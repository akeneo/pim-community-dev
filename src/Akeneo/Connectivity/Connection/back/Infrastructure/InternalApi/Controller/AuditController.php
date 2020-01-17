<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\InternalApi\Controller;

use Akeneo\Connectivity\Connection\Application\Audit\Query\CountDailyEventsByConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Audit\Query\CountDailyEventsByConnectionQuery;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\WeeklyEventCounts;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AuditController
{
    /** @var CountDailyEventsByConnectionHandler */
    private $countDailyEventsByConnectionHandler;

    public function __construct(CountDailyEventsByConnectionHandler $countDailyEventsByConnectionHandler)
    {
        $this->countDailyEventsByConnectionHandler = $countDailyEventsByConnectionHandler;
    }

    public function sourceConnectionsEvent(Request $request): JsonResponse
    {
        $eventType = $request->get('event_type', '');
        $endDate = $request->get(
            'end_date',
            (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d')
        );
        $startDate = (new \DateTime($endDate, new \DateTimeZone('UTC')))->modify('-7 day')->format('Y-m-d');

        $query = new CountDailyEventsByConnectionQuery($eventType, $startDate, $endDate);
        $countDailyEventsByConnection = $this->countDailyEventsByConnectionHandler->handle($query);

        $data = \array_reduce(
            $countDailyEventsByConnection,
            function (array $data, WeeklyEventCounts $connectionEventCounts) {
                return array_merge($data, $connectionEventCounts->normalize());
            },
            []
        );

        return new JsonResponse($data);
    }
}
