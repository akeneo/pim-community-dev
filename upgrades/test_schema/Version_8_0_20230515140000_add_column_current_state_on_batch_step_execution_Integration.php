<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_8_0_20230515140000_add_column_current_state_on_batch_step_execution_Integration extends TestCase
{
    private const MIGRATION_NAME = '_8_0_20230515140000_add_column_current_state_on_batch_step_execution';

    use ExecuteMigrationTrait;

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    /** @test */
    public function it_adds_is_deactivated_column()
    {
        if ($this->hasCurrentStateColumn()) {
            $this->dropCurrentStateColumn();
        }

        $this->assertFalse($this->hasCurrentStateColumn());
        $this->reExecuteMigration(self::MIGRATION_NAME);
        $this->assertTrue($this->hasCurrentStateColumn());
    }


    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function hasCurrentStateColumn(): bool
    {
        return $this->connection->executeQuery(
            <<<SQL
                SHOW COLUMNS FROM akeneo_batch_step_execution LIKE 'current_state';
            SQL,
        )->rowCount() >= 1;
    }

    private function dropCurrentStateColumn(): void
    {
        $sql = <<<SQL
            ALTER TABLE akeneo_batch_step_execution DROP COLUMN current_state;
        SQL;

        $this->connection->executeQuery($sql);
    }
}
