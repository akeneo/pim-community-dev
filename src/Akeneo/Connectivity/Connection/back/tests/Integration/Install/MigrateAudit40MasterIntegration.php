<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Install;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\HourlyInterval;
use Akeneo\Connectivity\Connection\Infrastructure\Install\MigrateAudit40Master;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class MigrateAudit40MasterIntegration extends TestCase
{
    /** @var Connection */
    private $dbalConnection;

    /** @var MigrateAudit40Master */
    private $migrationAudit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrationAudit = $this->get('Akeneo\Connectivity\Connection\Infrastructure\Install\MigrateAudit40Master');
        $this->dbalConnection = $this->get('database_connection');
    }

    public function test_it_tells_it_needs_a_migration_if_table_audit_product_does_not_exist()
    {
        $this->ensureDbSchemaWithAuditTable();

        $this->assertTrue($this->migrationAudit->needsMigration());
    }

    public function test_it_tells_it_does_not_need_a_migration_if_table_audit_product_exist()
    {
        $this->ensureDbSchemaWithoutAuditTable();

        $this->assertFalse($this->migrationAudit->needsMigration());
    }

    public function test_it_migrates_db_and_returns_datetime_to_check()
    {
        $this->ensureDbSchemaWithAuditTable();
        $this->assertTrue($this->migrationAudit->needsMigration());

        $hourlyIntervals = $this->migrationAudit->migrateIfNeeded();
        $this->assertFalse($this->migrationAudit->needsMigration());
        $this->assertCount(193, $hourlyIntervals);

        $expectedLastHourlyInterval = HourlyInterval::createFromDateTime(new \DateTime('now', new \DateTimeZone('UTC')));
        $this->assertTrue(HourlyInterval::equals($expectedLastHourlyInterval, $hourlyIntervals[0]));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function ensureDbSchemaWithAuditTable(): void
    {
        $this->dbalConnection->exec('CREATE TABLE IF NOT EXISTS akeneo_connectivity_connection_audit(connection_code VARCHAR(100) NOT NULL)');
        $this->dbalConnection->exec('DROP TABLE IF EXISTS akeneo_connectivity_connection_audit_product');
    }

    private function ensureDbSchemaWithoutAuditTable(): void
    {
        $this->dbalConnection->exec('CREATE TABLE IF NOT EXISTS akeneo_connectivity_connection_audit_product(connection_code VARCHAR(100) NOT NULL)');
        $this->dbalConnection->exec('DROP TABLE IF EXISTS akeneo_connectivity_connection_audit');
    }
}
