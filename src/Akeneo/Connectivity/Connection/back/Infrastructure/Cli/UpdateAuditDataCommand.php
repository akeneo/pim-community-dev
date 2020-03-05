<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Cli;

use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateProductEventCountCommand;
use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateProductEventCountHandler;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\HourlyInterval;
use Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query\DbalSelectHourlyIntervalsToRefreshQuery;
use Doctrine\DBAL\Connection;
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

    /** @var Connection */
    private $dbalConnection;

    public function __construct(
        UpdateProductEventCountHandler $updateProductEventCountHandler,
        DbalSelectHourlyIntervalsToRefreshQuery $selectHourlyIntervalsToRefreshQuery,
        Connection $dbalConnection
    ) {
        parent::__construct();

        $this->updateProductEventCountHandler = $updateProductEventCountHandler;
        $this->selectHourlyIntervalsToRefreshQuery = $selectHourlyIntervalsToRefreshQuery;
        $this->dbalConnection = $dbalConnection;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // TODO: To remove when pullup on master
        $this->migrate();

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

    private function migrate()
    {
        if ($this->needsMigration()) {
            $createNewAuditTableSql = <<<SQL
CREATE TABLE IF NOT EXISTS akeneo_connectivity_connection_audit_product(
    connection_code VARCHAR(100) NOT NULL,
    event_datetime DATETIME NOT NULL,
    event_count INT NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    updated DATETIME NOT NULL,
    PRIMARY KEY (connection_code, event_datetime, event_type)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC
SQL;

            $this->dbalConnection->exec($createNewAuditTableSql);
            $this->dbalConnection->exec('DROP TABLE akeneo_connectivity_connection_audit');

            $this->recalculateAuditForLastDays();
        }
    }

    private function needsMigration(): bool
    {
        $checkDbSchemaSql = <<<SQL
SELECT table_name FROM information_schema.tables 
WHERE table_name = 'akeneo_connectivity_connection_audit_product'
SQL;
        return false === $this->dbalConnection->fetchArray($checkDbSchemaSql);
    }

    private function recalculateAuditForLastDays()
    {
        $datetime = new \DateTime('now', new \DateTimeZone('UTC'));
        $datetime->setTime((int) $datetime->format('H'), 0);
        $hourInterval = new \DateInterval('PT1H');

        for ($i = 24*8; $i > 0; $i--) {
            $command = new UpdateProductEventCountCommand(
                HourlyInterval::createFromDateTime($datetime->sub($hourInterval))
            );
            $this->updateProductEventCountHandler->handle($command);
        }
    }
}
