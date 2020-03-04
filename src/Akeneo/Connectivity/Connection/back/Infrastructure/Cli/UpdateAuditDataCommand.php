<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Cli;

use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateProductEventCountCommand;
use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateProductEventCountHandler;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\HourlyInterval;
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

    public function __construct(
        UpdateProductEventCountHandler $updateProductEventCountHandler,
        DbalSelectHourlyIntervalsToRefreshQuery $selectHourlyIntervalsToRefreshQuery
    ) {
        parent::__construct();

        $this->updateProductEventCountHandler = $updateProductEventCountHandler;
        $this->selectHourlyIntervalsToRefreshQuery = $selectHourlyIntervalsToRefreshQuery;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var UpdateProductEventCountCommand[] */
        $commands = [];

        // Create a Command for the current hour.
        $commands[] = new UpdateProductEventCountCommand(
            HourlyInterval::createFromDateTime(new \DateTimeImmutable('now', new \DateTimeZone('UTC')))
        );

        /*
         * Create a Command for each hour retrieved from events that are not yet complete.
         * I.e., the last update happened before the end of the event datetime and need to be updated again.
         */
        $hourlyIntervalsToRefresh = $this->selectHourlyIntervalsToRefreshQuery->execute();
        foreach ($hourlyIntervalsToRefresh as $hourlyInterval) {
            // Ignore the current hour; already added.
            if (true === HourlyInterval::equals($commands[0]->hourlyInterval(), $hourlyInterval)) {
                continue;
            }

            $commands[] = new UpdateProductEventCountCommand($hourlyInterval);
        }

        foreach ($commands as $command) {
            $this->updateProductEventCountHandler->handle($command);
        }

        return 0;
    }
}
