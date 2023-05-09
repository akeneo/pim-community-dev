<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Audit;

use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataSourceProductEventCountCommand;
use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataSourceProductEventCountHandler;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\PurgeAuditProductQueryInterface;
use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;
use Akeneo\Connectivity\Connection\Infrastructure\Audit\Persistence\DbalSelectHourlyIntervalsToRefreshQuery;
use Psr\Log\LoggerInterface;

/**
 * @author    JM Leroux <jean-marie.leroux@akneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class UpdateAuditData
{
    public function __construct(
        private UpdateDataSourceProductEventCountHandler $updateDataSourceProductEventCountHandler,
        private DbalSelectHourlyIntervalsToRefreshQuery $selectHourlyIntervalsToRefreshQuery,
        private PurgeAuditProductQueryInterface $purgeQuery,
        private LoggerInterface $logger,
    ) {
    }

    public function execute(): void
    {
        $this->logger->info('Start audit data purge');

        $this->purgeOlderThanXDays(10);

        // Create a Command for the previous hour.
        $previousHourlyInterval = HourlyInterval::createFromDateTime(
            new \DateTimeImmutable('now -1 hour', new \DateTimeZone('UTC'))
        );
        $this->updateProductEventCount($previousHourlyInterval);

        // Create a Command for the previous hour.
        $previousHourlyInterval = HourlyInterval::createFromDateTime(
            new \DateTimeImmutable('now -1 hour', new \DateTimeZone('UTC'))
        );
        $this->updateProductEventCount($previousHourlyInterval);

        // Create a Command for the current hour.
        $currentHourlyInterval = HourlyInterval::createFromDateTime(
            new \DateTimeImmutable('now', new \DateTimeZone('UTC'))
        );
        $this->updateProductEventCount($currentHourlyInterval);

        /*
         * Create a Command for each hour retrieved from events that are not yet complete.
         * I.e., the last update happened before the end of the event and need to be updated again.
         */
        $hourlyIntervalsToRefresh = $this->selectHourlyIntervalsToRefreshQuery->execute();
        foreach ($hourlyIntervalsToRefresh as $hourlyInterval) {
            // Ignore the current and previous hour; already added.
            if ($currentHourlyInterval->equals($hourlyInterval)
                || $previousHourlyInterval->equals($hourlyInterval)
            ) {
                continue;
            }

            $this->updateProductEventCount($hourlyInterval);
        }

        $this->logger->info('End audit data purge');
    }

    private function purgeOlderThanXDays(int $days): void
    {
        $before = new \DateTimeImmutable("now - $days days", new \DateTimeZone('UTC'));
        $before = $before->setTime((int) $before->format('H'), 0, 0);

        $this->purgeQuery->execute($before);
    }

    private function updateProductEventCount(HourlyInterval $hourlyInterval): void
    {
        $this->updateDataSourceProductEventCountHandler->handle(
            new UpdateDataSourceProductEventCountCommand($hourlyInterval)
        );
    }
}
