<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\InternalApi\Controller;

use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SearchEventSubscriptionDebugLogsQueryInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventsApiDebugController
{
    private SearchEventSubscriptionDebugLogsQueryInterface $searchEventSubscriptionDebugLogsQuery;
    private SecurityFacade $securityFacade;

    public function __construct(
        SearchEventSubscriptionDebugLogsQueryInterface $searchEventSubscriptionDebugLogsQuery,
        SecurityFacade $securityFacade
    ) {
        $this->searchEventSubscriptionDebugLogsQuery = $searchEventSubscriptionDebugLogsQuery;
        $this->securityFacade = $securityFacade;
    }

    public function searchEventSubscriptionLogs(Request $request): Response
    {
        if (true !== $this->securityFacade->isGranted('akeneo_connectivity_connection_manage_settings')) {
            throw new AccessDeniedException();
        }

        $connectionCode = $request->query->get('connection_code');
        $searchAfter = $request->query->get('search_after');

        $logs = $this->searchEventSubscriptionDebugLogsQuery->execute($connectionCode, $searchAfter);

        return new JsonResponse($logs);
    }
}
