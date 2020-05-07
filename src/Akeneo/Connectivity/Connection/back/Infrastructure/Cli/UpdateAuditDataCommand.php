<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Cli;

use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataSourceProductEventCountCommand;
use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataSourceProductEventCountHandler;
use Akeneo\Connectivity\Connection\Domain\Common\HourlyInterval;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\PurgeAuditProductQuery;
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
    protected static $defaultName = 'akeneo:connectivity-audit:update-data';

    /** @var UpdateDataSourceProductEventCountHandler */
    private $updateDataSourceProductEventCountHandler;

    /** @var DbalSelectHourlyIntervalsToRefreshQuery */
    private $selectHourlyIntervalsToRefreshQuery;

    /** @var PurgeAuditProductQuery */
    private $purgeQuery;

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
        // Create a Command for the current hour.
        $nowHourlyInterval = HourlyInterval::createFromDateTime(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));
        $this->updateProductEventCount($nowHourlyInterval);

        /*
         * Create a Command for each hour retrieved from events that are not yet complete.
         * I.e., the last update happened before the end of the event and need to be updated again.
         */
        $hourlyIntervalsToRefresh = $this->selectHourlyIntervalsToRefreshQuery->execute();
        foreach ($hourlyIntervalsToRefresh as $hourlyInterval) {
            // Ignore the current hour; already added.
            if (true === $nowHourlyInterval->equals($hourlyInterval)) {
                continue;
            }

            $this->updateProductEventCount($hourlyInterval);
        }

        return 0;
    }

    private function purgeOlderThanXDays(int $days): void
    {
        $before = new \DateTimeImmutable("now - $days days", new \DateTimeZone('UTC'));
        $before->setTime((int) $before->format('H'), 0, 0);
        $this->purgeQuery->execute($before);
    }

    private function updateProductEventCount(HourlyInterval $hourlyInterval): void
    {
        $this->updateDataSourceProductEventCountHandler->handle(
            new UpdateDataSourceProductEventCountCommand($hourlyInterval)
        );
    }
}
