<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\InternalApi\Controller;

use Akeneo\Connectivity\Connection\Application\Audit\Query\CountDailyEventsByConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Audit\Query\CountDailyEventsByConnectionQuery;
use Akeneo\Connectivity\Connection\Infrastructure\AggregateProductEventCounts;
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
        $endDateUser = $request->get('end_date');
        $timezone = new \DateTimeZone($this->userContext->getUserTimezone());

        if (null === $endDateUser) {
            $endDateUser = (new \DateTimeImmutable('now', new \DateTimeZone($timezone)))->format('Y-m-d');
        }

        [$startDateTimeUser, $endDateTimeUser] = $this->createUserDateTimeInterval($endDateUser, $timezone);
        [$fromDateTime, $upToDateTime] = $this->createUtcDateTimeInterval($startDateTimeUser, $endDateTimeUser);

        $query = new CountDailyEventsByConnectionQuery($eventType, $fromDateTime, $upToDateTime);
        $periodEventCounts = $this->countDailyEventsByConnectionHandler->handle($query);

        $data = AggregateProductEventCounts::normalize($startDateTimeUser, $endDateTimeUser, $timezone, $periodEventCounts);

        return new JsonResponse($data);
    }

    private function createUserDateTimeInterval(string $endDateUser, \DateTimeZone $timezone): array
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

        $startDateTimeUser = $endDateTimeUser->sub(new \DateInterval('P7D'));

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
