<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook\EventsApiDebug\Controller\Internal;

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
final class SearchEventSubscriptionLogsAction
{
    public function __construct(
        private SearchEventSubscriptionDebugLogsQueryInterface $searchEventSubscriptionDebugLogsQuery,
        private SecurityFacade $securityFacade,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$this->securityFacade->isGranted('akeneo_connectivity_connection_manage_settings')) {
            throw new AccessDeniedException();
        }

        $connectionCode = $request->query->get('connection_code');
        $searchAfter = $request->query->get('search_after');
        $filters = \json_decode($request->query->get('filters', ''), true, 512, JSON_THROW_ON_ERROR) ?: [];

        $logs = $this->searchEventSubscriptionDebugLogsQuery->execute($connectionCode, $searchAfter, $filters);

        return new JsonResponse($logs);
    }
}
