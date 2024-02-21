<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_7_0_20220818143128_remove_zdd_setidentifiernullable_from_pim_one_time_task_table_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    public function test_it_deletes_the_zdd_migration()
    {
        $this->givenZddMigrationHasBeenPlayed();
        $this->reExecuteMigration('_7_0_20220818143128_remove_zdd_setidentifiernullable_from_pim_one_time_task_table');
        $this->assertZddMigrationRemoved();
    }


    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function givenZddMigrationHasBeenPlayed(): void
    {
        $zddMigration = <<<SQL
REPLACE INTO `pim_one_time_task` (`code`, `status`, `start_time`, `end_time`, `values`)
VALUES
	('zdd_SetProductIdentifierNullable', 'finished', '2022-08-17 17:19:48', NULL, '{}');
SQL;
        $this->connection->executeStatement($zddMigration);
    }

    private function assertZddMigrationRemoved(): void
    {
        $sql = 'SELECT 1 FROM pim_one_time_task WHERE code = "zdd_SetProductIdentifierNullable";';
        $result = (bool) $this->connection->executeQuery($sql)->fetchOne();
        $this->assertFalse($result);
    }
}
