<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\InternalApi\Controller;

use Akeneo\Connectivity\Connection\Application\Audit\Query\CountDailyEventsByConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Audit\Query\CountDailyEventsByConnectionQuery;
use Akeneo\Connectivity\Connection\Application\Audit\Query\GetErrorCountPerConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Audit\Query\GetErrorCountPerConnectionQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Audit\AggregateProductEventCounts;
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
    /** @var UserContext */
    private $userContext;

    /** @var CountDailyEventsByConnectionHandler */
    private $countDailyEventsByConnectionHandler;

    /** @var GetErrorCountPerConnectionHandler */
    private $getErrorCountPerConnectionHandler;

    public function __construct(
        UserContext $userContext,
        CountDailyEventsByConnectionHandler $countDailyEventsByConnectionHandler,
        GetErrorCountPerConnectionHandler $getErrorCountPerConnectionHandler
    ) {
        $this->userContext = $userContext;
        $this->countDailyEventsByConnectionHandler = $countDailyEventsByConnectionHandler;
        $this->getErrorCountPerConnectionHandler = $getErrorCountPerConnectionHandler;
    }

    public function getWeeklyAudit(Request $request): JsonResponse
    {
        $timezone = new \DateTimeZone($this->userContext->getUserTimezone());

        $eventType = $request->get('event_type');
        $endDateUser = $request->get(
            'end_date',
            (new \DateTimeImmutable('now', $timezone))->format('Y-m-d')
        );

        [$startDateTimeUser, $endDateTimeUser] = $this->createUserDateTimeInterval($endDateUser, $timezone, new \DateInterval('P7D'));
        [$fromDateTime, $upToDateTime] = $this->createUtcDateTimeInterval($startDateTimeUser, $endDateTimeUser);

        $query = new CountDailyEventsByConnectionQuery($eventType, $fromDateTime, $upToDateTime);
        $periodEventCounts = $this->countDailyEventsByConnectionHandler->handle($query);

        $data = AggregateProductEventCounts::normalize($periodEventCounts, $timezone);

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

        [$startDateTimeUser, $endDateTimeUser] = $this->createUserDateTimeInterval($endDateUser, $timezone, new \DateInterval('P6D'));
        [$fromDateTime, $upToDateTime] = $this->createUtcDateTimeInterval($startDateTimeUser, $endDateTimeUser);

        $query = new GetErrorCountPerConnectionQuery($errorType, $fromDateTime, $upToDateTime);
        $errorCountPerConnection = $this->getErrorCountPerConnectionHandler->handle($query);

        $data = $errorCountPerConnection->normalize();

        return new JsonResponse($data);
    }

    private function createUserDateTimeInterval(string $endDateUser, \DateTimeZone $timezone, \DateInterval $dateInterval): array
    {
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

    private function createUtcDateTimeInterval(\DateTimeImmutable $startDateTimeUser, \DateTimeImmutable $endDateTimeUser): array
    {
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
