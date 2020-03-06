<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\InternalApi\Controller;

use Akeneo\Connectivity\Connection\Application\Audit\Query\CountDailyEventsByConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Audit\Query\CountDailyEventsByConnectionQuery;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\WeeklyEventCounts;
use Akeneo\UserManagement\Bundle\Context\UserContext;
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

    /** @var UserContext */
    private $userContext;

    public function __construct(
        CountDailyEventsByConnectionHandler $countDailyEventsByConnectionHandler,
        UserContext $userContext
    ) {
        $this->userContext = $userContext;
        $this->countDailyEventsByConnectionHandler = $countDailyEventsByConnectionHandler;
    }

    public function sourceConnectionsEvent(Request $request): JsonResponse
    {
        $eventType = $request->get('event_type');

        $timezone = $this->userContext->getUserTimezone();
        $endDateTime = \DateTimeImmutable::createFromFormat(
            'Y-m-d',
            $request->get('end_date', date('Y-m-d')),
            new \DateTimeZone($timezone)
        );

        $startDate = $endDateTime->sub(new \DateInterval('P7D'))->format('Y-m-d');
        $endDate = $endDateTime->format('Y-m-d');

        $query = new CountDailyEventsByConnectionQuery($eventType, $startDate, $endDate, $timezone);
        $dailyEventCountsPerConnection = $this->countDailyEventsByConnectionHandler->handle($query);

        $data = \array_reduce(
            $dailyEventCountsPerConnection,
            function (array $data, WeeklyEventCounts $weeklyEventCounts) {
                return array_merge($data, $weeklyEventCounts->normalize());
            },
            []
        );

        return new JsonResponse($data);
    }
}
