<?php

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_7_0_20221212160000_add_database_install_time_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private Connection $connection;
    private const MIGRATION_LABEL = '_7_0_20221212160000_add_database_install_time';

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    public function test_it_add_db_install_time(): void
    {
        $this->rollback();
        Assert::assertEmpty($this->getInstallData());
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertNotEmpty($this->getInstallData());
        Assert::assertArrayHasKey('database_installed_at', \json_decode($this->getInstallData(), true));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function rollback(): void
    {
        $this->connection->executeQuery('DELETE FROM pim_configuration WHERE code = "install_data";');
    }

    private function getInstallData(): ?string
    {
        $result = $this->connection->executeQuery(
            'SELECT `values` FROM pim_configuration WHERE code = "install_data";'
        );

        return $result->fetchOne();
    }
}
