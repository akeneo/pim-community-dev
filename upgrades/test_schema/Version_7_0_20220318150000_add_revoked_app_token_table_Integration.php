<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;

class Version_7_0_20220318150000_add_revoked_app_token_table_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20220318150000_add_revoked_app_token_table';

    public function test_revoked_app_token_table_is_created(): void
    {
        $this->get('database_connection')->executeQuery(<<<SQL
DROP TABLE IF EXISTS akeneo_connectivity_revoked_app_token;
SQL);

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $schemaManager = $this->get('database_connection')->getSchemaManager();
        $this->assertTrue($schemaManager->tablesExist('akeneo_connectivity_revoked_app_token'));
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
