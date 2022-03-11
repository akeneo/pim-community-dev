<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Audit\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Audit\Query\GetErrorCountPerConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Audit\Query\GetErrorCountPerConnectionQuery;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class GetErrorCountPerConnectionAction extends AbstractAuditAction
{
    public function __construct(
        private UserContext $userContext,
        private GetErrorCountPerConnectionHandler $getErrorCountPerConnectionHandler,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
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
}
