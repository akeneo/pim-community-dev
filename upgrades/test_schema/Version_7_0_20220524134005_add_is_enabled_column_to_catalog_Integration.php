<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;

final class Version_7_0_20220524134005_add_is_enabled_column_to_catalog_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20220524134005_add_is_enabled_column_to_catalog';

    protected function getConfiguration()
    {
        return null;
    }

    public function test_it_adds_is_enabled_column(): void
    {
        $this->removeIsEnabledColumn();

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertTrue($this->isEnabledColumnExists());
    }

    private function isEnabledColumnExists(): bool
    {
        $rows = $this->get('database_connection')->fetchAllAssociative(
            <<<SQL
                SHOW COLUMNS FROM akeneo_catalog LIKE 'is_enabled'
            SQL,
        );

        return count($rows) >= 1;
    }

    private function removeIsEnabledColumn(): void
    {
        if (!$this->isEnabledColumnExists()) {
            return;
        }

        $this->get('database_connection')->executeQuery(
            <<<SQL
                ALTER TABLE akeneo_catalog DROP COLUMN is_enabled;
            SQL
        );
    }
}
