<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Audit\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Audit\Query\GetPeriodEventCountPerConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Audit\Query\GetPeriodEventCountPerConnectionQuery;
use Akeneo\Connectivity\Connection\Domain\ValueObject\DateTimePeriod;
use Akeneo\Connectivity\Connection\Infrastructure\Audit\AggregateAuditData;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class GetWeeklyAuditAction extends AbstractAuditAction
{
    public function __construct(
        private UserContext $userContext,
        private GetPeriodEventCountPerConnectionHandler $getPeriodEventCountPerConnectionHandler,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $timezone = new \DateTimeZone($this->userContext->getUserTimezone());

        $eventType = $request->get('event_type');
        $endDateUser = $request->get(
            'end_date',
            (new \DateTimeImmutable('now', $timezone))->format('Y-m-d')
        );

        [$startDateTimeUser, $endDateTimeUser] = $this->createUserDateTimeInterval(
            $endDateUser,
            $timezone,
            new \DateInterval('P7D')
        );
        [$fromDateTime, $upToDateTime] = $this->createUtcDateTimeInterval($startDateTimeUser, $endDateTimeUser);

        $query = new GetPeriodEventCountPerConnectionQuery($eventType, new DateTimePeriod($fromDateTime, $upToDateTime));
        $periodEventCounts = $this->getPeriodEventCountPerConnectionHandler->handle($query);

        $data = AggregateAuditData::normalize($periodEventCounts, $timezone);

        // TODO To remove after the UI is updated with the new format.
        $retroCompatibleData = [];
        foreach ($data as $connectionCode => $connectionData) {
            $retroCompatibleData[$connectionCode] = [
                'daily' => \array_merge($connectionData['previous_week'], $connectionData['current_week']),
                'weekly_total' => $connectionData['current_week_total']
            ];
        }

        return new JsonResponse($retroCompatibleData);
    }
}
