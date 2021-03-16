<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\InternalApi\Controller;

use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\GetAllEventSubscriptionDebugLogsQueryInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventsApiDebugController
{
    private GetAllEventSubscriptionDebugLogsQueryInterface $getEventSubscriptionLogsQuery;
    private SecurityFacade $securityFacade;

    public function __construct(
        GetAllEventSubscriptionDebugLogsQueryInterface $getEventSubscriptionLogsQuery,
        SecurityFacade $securityFacade
    ) {
        $this->getEventSubscriptionLogsQuery = $getEventSubscriptionLogsQuery;
        $this->securityFacade = $securityFacade;
    }

    public function downloadEventSubscriptionLogs(Request $request): Response
    {
        if (true !== $this->securityFacade->isGranted('akeneo_connectivity_connection_manage_settings')) {
            throw new AccessDeniedException();
        }

        $connectionCode = $request->query->get('connection_code');

        $logs = $this->getEventSubscriptionLogsQuery->execute($connectionCode);

        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            sprintf('events_api_logs_%s.txt', date('Ymd_His'))
        );

        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'text/plain');
        $response->headers->set('Content-Disposition', $disposition);

        $response->setCallback(
            function () use ($logs) {
                /**
                 * @var array{
                 *  timestamp: int,
                 *  level: string,
                 *  message: string,
                 *  connection_code: ?string,
                 *  context: array
                 * } $log
                 */
                foreach ($logs as $log) {
                    echo sprintf(
                        '%s %s %s %s%s',
                        \DateTime::createFromFormat(
                            'U',
                            (string)$log['timestamp'],
                            new \DateTimeZone('UTC')
                        )->format('Y/m/d H:i:s'),
                        strtoupper($log['level']),
                        $log['message'],
                        json_encode($log['context']),
                        PHP_EOL
                    );
                    flush();
                }
            }
        );

        return $response;
    }
}
