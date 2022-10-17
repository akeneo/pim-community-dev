<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/** @group migration-supplier-portal */
final class Version_7_0_20221007115352_supplier_portal_add_contributor_account_consent_field_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20221007115352_supplier_portal_add_contributor_account_consent_field';

    /** @test */
    public function it_adds_the_consent_column(): void
    {
        $query = <<<SQL
            ALTER TABLE akeneo_supplier_portal_contributor_account 
            DROP COLUMN consent;
        SQL;

        $this->get('database_connection')->executeQuery($query);

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $schemaManager = $this->get('database_connection')->getSchemaManager();
        $tableColumns = $schemaManager->listTableColumns('akeneo_supplier_portal_contributor_account');

        $this->assertArrayHasKey('consent', $tableColumns, 'The column `consent` should have been added to table `akeneo_supplier_portal_contributor_account`');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
