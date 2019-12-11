<?php

declare(strict_types=1);

namespace Akeneo\Apps\Infrastructure\InternalApi\Controller;

use Akeneo\Apps\Application\Audit\Query\CountDailyEventsByAppHandler;
use Akeneo\Apps\Application\Audit\Query\CountDailyEventsByAppQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AuditController
{
    /** @var CountDailyEventsByAppHandler */
    private $countDailyEventsByAppHandler;

    public function __construct(CountDailyEventsByAppHandler $countDailyEventsByAppHandler)
    {
        $this->countDailyEventsByAppHandler = $countDailyEventsByAppHandler;
    }

    public function sourceAppsEvent(Request $request): JsonResponse
    {
        $eventType = $request->get('event_type', '');
        $today = new \DateTime('now', new \DateTimeZone('UTC'));
        $startPeriod = $today->modify('-1 week');

        $query = new CountDailyEventsByAppQuery($eventType, $startPeriod->format('Y-m-d'), $today->format('Y-m-d'));
        $countDailyEventsByApp = $this->countDailyEventsByAppHandler->handle($query);




        return new JsonResponse();
//        return new JsonResponse(
//            array_map(function (App $app) {
//                return $app->normalize();
//            }, $apps)
//        );
    }
}
