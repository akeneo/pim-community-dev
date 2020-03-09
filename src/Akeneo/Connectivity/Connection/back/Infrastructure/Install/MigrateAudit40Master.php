<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Install;

use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateProductEventCountCommand;
use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateProductEventCountHandler;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\HourlyInterval;
use Doctrine\DBAL\Connection;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 * @todo: Remove this class when pulling up 4.0 on master
 */
class MigrateAudit40Master
{
    /** @var Connection */
    private $dbalConnection;

    /** @var UpdateProductEventCountHandler */
    private $updateProductEventCountHandler;

    public function __construct(
        Connection $dbalConnection,
        UpdateProductEventCountHandler $updateProductEventCountHandler
    ) {
        $this->dbalConnection = $dbalConnection;
        $this->updateProductEventCountHandler = $updateProductEventCountHandler;
    }

    public function migrateIfNeeded(): void
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

    public function needsMigration(): bool
    {
        $checkDbSchemaSql = <<<SQL
SELECT table_name FROM information_schema.tables 
WHERE table_name = 'akeneo_connectivity_connection_audit_product'
SQL;
        return false === $this->dbalConnection->fetchArray($checkDbSchemaSql);
    }

    private function recalculateAuditForLastDays(): void
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
