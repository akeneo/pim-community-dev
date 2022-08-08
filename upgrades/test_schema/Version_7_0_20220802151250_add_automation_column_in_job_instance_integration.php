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

final class Version_7_0_20220802151250_add_automation_column_in_job_instance_integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20220802151250_add_automation_column_in_job_instance';

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    public function testItAddAutomationColumnOfTypeJson()
    {
        $this->removeAutomationColumn('akeneo_batch_job_instance');
        $this->removeScheduledColumn('akeneo_batch_job_instance');

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertTrue($this->automationColumnExist('akeneo_batch_job_instance'));
        $this->assertTrue($this->scheduledColumnExist('akeneo_batch_job_instance'));
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
        if (!$this->automationColumnExist($tableName)) {
            return;
        }

        $this->get('database_connection')->executeQuery(
            <<<SQL
                ALTER TABLE $tableName DROP COLUMN scheduled;
            SQL
        );
    }

    private function automationColumnExist(string $tableName): bool
    {
        $rows = $this->get('database_connection')->fetchAllAssociative(
            <<<SQL
                SHOW COLUMNS FROM $tableName LIKE 'automation'
            SQL,
        );

        return count($rows) >= 1;
    }

    private function scheduledColumnExist(string $tableName): bool
    {
        $rows = $this->get('database_connection')->fetchAllAssociative(
            <<<SQL
                SHOW COLUMNS FROM $tableName LIKE 'scheduled'
            SQL,
        );

        return count($rows) >= 1;
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
