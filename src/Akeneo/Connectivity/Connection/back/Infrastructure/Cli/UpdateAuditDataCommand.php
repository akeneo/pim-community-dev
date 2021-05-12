<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Cli;

use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataSourceProductEventCountCommand;
use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataSourceProductEventCountHandler;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\PurgeAuditProductQuery;
use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;
use Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query\DbalSelectHourlyIntervalsToRefreshQuery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateAuditDataCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'akeneo:connectivity-audit:update-data';

    private UpdateDataSourceProductEventCountHandler $updateDataSourceProductEventCountHandler;

    private DbalSelectHourlyIntervalsToRefreshQuery $selectHourlyIntervalsToRefreshQuery;

    private PurgeAuditProductQuery $purgeQuery;

    public function __construct(
        UpdateDataSourceProductEventCountHandler $updateDataSourceProductEventCountHandler,
        DbalSelectHourlyIntervalsToRefreshQuery $selectHourlyIntervalsToRefreshQuery,
        PurgeAuditProductQuery $purgeQuery
    ) {
        parent::__construct();

        $this->updateDataSourceProductEventCountHandler = $updateDataSourceProductEventCountHandler;
        $this->selectHourlyIntervalsToRefreshQuery = $selectHourlyIntervalsToRefreshQuery;
        $this->purgeQuery = $purgeQuery;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
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
            if (true === $currentHourlyInterval->equals($hourlyInterval)
                || true === $previousHourlyInterval->equals($hourlyInterval)
            ) {
                continue;
            }

            $this->updateProductEventCount($hourlyInterval);
        }

        return 0;
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
