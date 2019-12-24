<?php

declare(strict_types=1);

namespace Akeneo\Apps\Infrastructure\InternalApi\Controller;

use Akeneo\Apps\Application\Audit\Query\CountDailyEventsByConnectionHandler;
use Akeneo\Apps\Application\Audit\Query\CountDailyEventsByConnectionQuery;
use Akeneo\Apps\Domain\Audit\Model\Read\WeeklyEventCounts;
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
        $startPeriod = new \DateTime('-7 days', new \DateTimeZone('UTC'));
        $today = new \DateTime('now', new \DateTimeZone('UTC'));

        $query = new CountDailyEventsByConnectionQuery($eventType, $startPeriod->format('Y-m-d'), $today->format('Y-m-d'));
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
