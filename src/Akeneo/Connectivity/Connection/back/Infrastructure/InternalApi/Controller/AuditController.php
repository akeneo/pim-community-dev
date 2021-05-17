<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\InternalApi\Controller;

use Akeneo\Connectivity\Connection\Application\Audit\Query\GetErrorCountPerConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Audit\Query\GetErrorCountPerConnectionQuery;
use Akeneo\Connectivity\Connection\Application\Audit\Query\GetPeriodErrorCountPerConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Audit\Query\GetPeriodErrorCountPerConnectionQuery;
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
class AuditController
{
    private UserContext $userContext;

    private GetPeriodEventCountPerConnectionHandler $getPeriodEventCountPerConnectionHandler;

    private GetErrorCountPerConnectionHandler $getErrorCountPerConnectionHandler;

    private GetPeriodErrorCountPerConnectionHandler $getPeriodErrorCountPerConnectionHandler;

    public function __construct(
        UserContext $userContext,
        GetPeriodEventCountPerConnectionHandler $getPeriodEventCountPerConnectionHandler,
        GetErrorCountPerConnectionHandler $getErrorCountPerConnectionHandler,
        GetPeriodErrorCountPerConnectionHandler $getPeriodErrorCountPerConnectionHandler
    ) {
        $this->userContext = $userContext;
        $this->getPeriodEventCountPerConnectionHandler = $getPeriodEventCountPerConnectionHandler;
        $this->getErrorCountPerConnectionHandler = $getErrorCountPerConnectionHandler;
        $this->getPeriodErrorCountPerConnectionHandler = $getPeriodErrorCountPerConnectionHandler;
    }

    public function getWeeklyAudit(Request $request): JsonResponse
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
                'daily' => array_merge($connectionData['previous_week'], $connectionData['current_week']),
                'weekly_total' => $connectionData['current_week_total']
            ];
        }

        return new JsonResponse($retroCompatibleData);
    }

    public function getWeeklyErrorAudit(Request $request): JsonResponse
    {
        $timezone = new \DateTimeZone($this->userContext->getUserTimezone());

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

        $query = new GetPeriodErrorCountPerConnectionQuery(new DateTimePeriod($fromDateTime, $upToDateTime));
        $periodEventCountPerConnection = $this->getPeriodErrorCountPerConnectionHandler->handle($query);

        $data = AggregateAuditData::normalize($periodEventCountPerConnection, $timezone);

        return new JsonResponse($data);
    }

    public function getErrorCountPerConnection(Request $request): JsonResponse
    {
        $timezone = new \DateTimeZone($this->userContext->getUserTimezone());

        $errorType = $request->get('error_type');
        $endDateUser = $request->get(
            'end_date',
            (new \DateTimeImmutable('now', $timezone))->format('Y-m-d')
        );

        [$startDateTimeUser, $endDateTimeUser] = $this->createUserDateTimeInterval(
            $endDateUser,
            $timezone,
            new \DateInterval('P6D')
        );
        [$fromDateTime, $upToDateTime] = $this->createUtcDateTimeInterval($startDateTimeUser, $endDateTimeUser);

        $query = new GetErrorCountPerConnectionQuery($errorType, $fromDateTime, $upToDateTime);
        $errorCountPerConnection = $this->getErrorCountPerConnectionHandler->handle($query);

        $data = $errorCountPerConnection->normalize();

        return new JsonResponse($data);
    }

    /**
     * @return \DateTimeImmutable[]
     */
    private function createUserDateTimeInterval(
        string $endDateUser,
        \DateTimeZone $timezone,
        \DateInterval $dateInterval
    ): array {
        $endDateTimeUser = \DateTimeImmutable::createFromFormat(
            'Y-m-d',
            $endDateUser,
            $timezone
        );
        if (false === $endDateTimeUser) {
            throw new \InvalidArgumentException(sprintf(
                'Unexpected format for the `end_date` parameter "%s". Format must be `Y-m-d`',
                $endDateUser
            ));
        }

        $startDateTimeUser = $endDateTimeUser->sub($dateInterval);

        return [$startDateTimeUser, $endDateTimeUser];
    }

    /**
     * @return \DateTimeImmutable[]
     */
    private function createUtcDateTimeInterval(
        \DateTimeImmutable $startDateTimeUser,
        \DateTimeImmutable $endDateTimeUser
    ): array {
        $fromDateTime = $startDateTimeUser
            ->setTime(0, 0)
            ->setTimezone(new \DateTimeZone('UTC'));

        $upToDateTime = $endDateTimeUser
            ->setTime(0, 0)
            ->add(new \DateInterval('P1D'))
            ->setTimezone(new \DateTimeZone('UTC'));

        return [$fromDateTime, $upToDateTime];
    }
}
