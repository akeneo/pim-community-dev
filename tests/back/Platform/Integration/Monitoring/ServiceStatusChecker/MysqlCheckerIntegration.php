<?php
declare(strict_types=1);

namespace AkeneoTestEnterprise\Platform\Integration\Monitoring\ServiceStatusChecker;

use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\MysqlChecker;
use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\ServiceStatus;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use PHPUnit\Framework\Assert;

final class MysqlCheckerIntegration extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_mysql_is_ok_when_you_can_request_a_pim_table_without_error(): void
    {
        Assert::assertEquals(ServiceStatus::ok(), $this->getMysqlChecker()->status());
    }

    public function test_mysql_is_ko_when_you_cant_request_a_pim_table_without_error(): void
    {
        $this->getDatabaseConnection()->exec('RENAME TABLE pim_catalog_product_unique_data TO backup_pim_catalog_product_unique_data');
        $status = $this->getMysqlChecker()->status();
        Assert::assertStringContainsString('Unable to request the database', $status->getMessage());
        Assert::assertFalse($status->isOk());
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function tearDown(): void
    {
        try {
            $this->getDatabaseConnection()->exec('RENAME TABLE backup_pim_catalog_product_unique_data TO pim_catalog_product_unique_data');
        } catch (DBALException $e) {
        }

        $this->getDatabaseConnection()->exec("SELECT 'expected_table' FROM pim_catalog_product_unique_data");

        parent::tearDown();
    }

    private function getMysqlChecker(): MysqlChecker
    {
        return $this->get('Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\MysqlChecker');
    }

    private function getDatabaseConnection(): Connection
    {
        return $this->get('database_connection');
    }
}
