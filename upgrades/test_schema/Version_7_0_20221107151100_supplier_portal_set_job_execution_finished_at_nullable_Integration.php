<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/** @group migration-supplier-portal */
final class Version_7_0_20221107151100_supplier_portal_set_job_execution_finished_at_nullable_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20221107151100_supplier_portal_set_job_execution_finished_at_nullable';

    /** @test */
    public function it_adds_the_consent_column(): void
    {
        $query = <<<SQL
            ALTER TABLE akeneo_supplier_portal_product_file_imported_by_job_execution 
            MODIFY finished_at datetime NOT NULL;
        SQL;

        $this->get('database_connection')->executeQuery($query);

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $schemaManager = $this->get('database_connection')->getSchemaManager();
        $tableColumns = $schemaManager->listTableColumns('akeneo_supplier_portal_product_file_imported_by_job_execution');

        $this->assertFalse($tableColumns['finished_at']->getNotnull());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
