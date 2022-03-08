<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Audit\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Audit\Query\GetPeriodErrorCountPerConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Audit\Query\GetPeriodErrorCountPerConnectionQuery;
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
final class GetWeeklyErrorAuditAction extends AbstractAuditAction
{
    public function __construct(
        private UserContext $userContext,
        private GetPeriodErrorCountPerConnectionHandler $getPeriodErrorCountPerConnectionHandler,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
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
}
