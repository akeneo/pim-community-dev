<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Audit;

use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataSourceProductEventCountCommand;
use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataSourceProductEventCountHandler;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\PurgeAuditProductQueryInterface;
use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;
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
        private PurgeAuditProductQueryInterface $purgeQuery,
        private LoggerInterface $logger,
    ) {
    }

    public function execute(): void
    {
        $this->logger->info('Start audit data purge');

        $defaultStartDatetime = $this->getDefaultStartDatetime(10);

        $this->purgeOlderThanXDays($defaultStartDatetime);

        $hourlyIntervals = HourlyInterval::getHourlyIntervals(
            $defaultStartDatetime,
            new \DateTimeImmutable('now', new \DateTimeZone('UTC'))
        );

        foreach ($hourlyIntervals as $hourlyInterval) {
            $this->updateProductEventCount($hourlyInterval);
        }

        $this->logger->info('End audit data purge');
    }

    private function purgeOlderThanXDays(\DateTimeImmutable $before): void
    {
        $this->purgeQuery->execute($before);
    }

    private function updateProductEventCount(HourlyInterval $hourlyInterval): void
    {
        $this->updateDataSourceProductEventCountHandler->handle(
            new UpdateDataSourceProductEventCountCommand($hourlyInterval)
        );
    }

    private function getDefaultStartDatetime(int $days)
    {
        $before = new \DateTimeImmutable("now - $days days", new \DateTimeZone('UTC'));
        return $before->setTime((int) $before->format('H'), 0, 0);
    }
}
