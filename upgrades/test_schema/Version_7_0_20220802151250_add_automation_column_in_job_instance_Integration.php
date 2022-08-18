<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

final class Version_7_0_20220802151250_add_automation_column_in_job_instance_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20220802151250_add_automation_column_in_job_instance';
    private const TABLE_NAME = 'akeneo_batch_job_instance';

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    public function testItAddAutomationColumnOfTypeJson()
    {
        $this->removeAutomationColumn(self::TABLE_NAME);
        $this->removeIndex(self::TABLE_NAME);
        $this->removeScheduledColumn(self::TABLE_NAME);

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertTrue($this->automationColumnExist(self::TABLE_NAME));
        $this->assertTrue($this->scheduledColumnExist(self::TABLE_NAME));
        $this->assertTrue($this->scheduledIndexExist(self::TABLE_NAME));
    }

    private function removeAutomationColumn(string $tableName): void
    {
        if (!$this->automationColumnExist($tableName)) {
            return;
        }

        $this->get('database_connection')->executeQuery(
            <<<SQL
                ALTER TABLE $tableName DROP COLUMN automation;
            SQL
        );
    }

    private function removeScheduledColumn(string $tableName): void
    {
        if (!$this->scheduledColumnExist($tableName)) {
            return;
        }

        $this->get('database_connection')->executeQuery(
            <<<SQL
                ALTER TABLE $tableName DROP COLUMN scheduled;
            SQL
        );
    }

    private function removeIndex(string $tableName)
    {
        if (!$this->scheduledIndexExist(self::TABLE_NAME)) {
            return;
        }

        $this->get('database_connection')->executeQuery(
            <<<SQL
                DROP INDEX scheduled_idx ON $tableName;
            SQL
        );
    }

    private function scheduledIndexExist(string $tableName): bool
    {
        $rows = $this->get('database_connection')->fetchAllAssociative(
            <<<SQL
                SHOW INDEX FROM $tableName WHERE Key_name='scheduled_idx';
            SQL,
        );

        return count($rows) >= 1;
    }

    private function automationColumnExist(string $tableName): bool
    {
        $rows = $this->get('database_connection')->fetchAllAssociative(
            <<<SQL
                SHOW COLUMNS FROM $tableName LIKE 'automation';
            SQL,
        );

        return count($rows) >= 1;
    }

    private function scheduledColumnExist(string $tableName): bool
    {
        $rows = $this->get('database_connection')->fetchAllAssociative(
            <<<SQL
                SHOW COLUMNS FROM $tableName LIKE 'scheduled';
            SQL,
        );

        return count($rows) >= 1;
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
