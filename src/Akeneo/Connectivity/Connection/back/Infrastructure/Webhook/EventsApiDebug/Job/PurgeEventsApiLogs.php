<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook\EventsApiDebug\Job;

use Akeneo\Connectivity\Connection\Infrastructure\Webhook\EventsApiDebug\Persistence\PurgeEventsApiErrorLogsQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\EventsApiDebug\Persistence\PurgeEventsApiSuccessLogsQuery;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Psr\Log\LoggerInterface;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PurgeEventsApiLogs implements TaskletInterface
{
    protected StepExecution $stepExecution;

    public function __construct(
        private LoggerInterface $logger,
        private PurgeEventsApiSuccessLogsQuery $purgeSuccessLogsQuery,
        private PurgeEventsApiErrorLogsQuery $purgeErrorLogsQuery
    ) {
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute(): void
    {
        $this->logger->info('Start purge of events API logs');
        $this->purgeSuccessLogsQuery->execute();
        $this->purgeErrorLogsQuery->execute(
            (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))
                ->sub(new \DateInterval('PT72H'))
        );
        $this->logger->info('Purge of events API logs ended');
    }
}
