<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Cli;

use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateProductEventCountCommand;
use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateProductEventCountHandler;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\HourlyInterval;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\PurgeAuditProductQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Install\MigrateAudit40Master;
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

    /** @var UpdateProductEventCountHandler */
    private $updateProductEventCountHandler;

    /** @var DbalSelectHourlyIntervalsToRefreshQuery */
    private $selectHourlyIntervalsToRefreshQuery;

    /** @var MigrateAudit40Master */
    private $migrateAudit40Master;

    /** @var PurgeAuditProductQuery */
    private $purgeQuery;

    public function __construct(
        UpdateProductEventCountHandler $updateProductEventCountHandler,
        DbalSelectHourlyIntervalsToRefreshQuery $selectHourlyIntervalsToRefreshQuery,
        PurgeAuditProductQuery $purgeQuery,
        MigrateAudit40Master $migrateAudit40Master
    ) {
        parent::__construct();

        $this->updateProductEventCountHandler = $updateProductEventCountHandler;
        $this->selectHourlyIntervalsToRefreshQuery = $selectHourlyIntervalsToRefreshQuery;
        $this->migrateAudit40Master = $migrateAudit40Master;
        $this->purgeQuery = $purgeQuery;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // TODO: To remove when pullup on master
        $hourlyIntervalsToRecalculate = $this->migrateAudit40Master->migrateIfNeeded();
        $this->purgeOlderThanXDays(10);
        if (!empty($hourlyIntervalsToRecalculate)) {
            foreach ($hourlyIntervalsToRecalculate as $hourlyInterval) {
                $command = new UpdateProductEventCountCommand($hourlyInterval);
                $this->updateProductEventCountHandler->handle($command);
            }

            return 0;
        }

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
        $before->setTime((int) $before->format('H'), 0, 0);
        $this->purgeQuery->execute($before);
    }

    private function updateProductEventCount(HourlyInterval $hourlyInterval): void
    {
        $this->updateProductEventCountHandler->handle(
            new UpdateProductEventCountCommand($hourlyInterval)
        );
    }
}
