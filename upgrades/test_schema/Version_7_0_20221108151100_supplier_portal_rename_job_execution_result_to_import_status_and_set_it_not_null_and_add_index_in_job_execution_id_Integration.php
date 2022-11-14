<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/** @group migration-supplier-portal */
final class Version_7_0_20221108151100_supplier_portal_rename_job_execution_result_to_import_status_and_set_it_not_null_and_add_index_in_job_execution_id_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20221108151100_supplier_portal_rename_job_execution_result_to_import_status_and_set_it_not_null_and_add_index_in_job_execution_id';

    /** @test */
    public function it_execute_migration(): void
    {

        $query = <<<SQL
            ALTER TABLE akeneo_supplier_portal_product_file_imported_by_job_execution 
            MODIFY import_status VARCHAR(100) NULL;
        SQL;

        $this->get('database_connection')->executeQuery($query);

        $query = <<<SQL
            ALTER TABLE akeneo_supplier_portal_product_file_imported_by_job_execution 
            RENAME COLUMN import_status TO job_execution_result;
        SQL;

        $this->get('database_connection')->executeQuery($query);

        $query = <<<SQL
            DROP INDEX akeneo_supplier_portal_product_file_imported_execution_id_index 
                    ON akeneo_supplier_portal_product_file_imported_by_job_execution
        SQL;

        $this->get('database_connection')->executeQuery($query);

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $schemaManager = $this->get('database_connection')->getSchemaManager();

        $tableColumns = $schemaManager->listTableColumns('akeneo_supplier_portal_product_file_imported_by_job_execution');

        $index = $schemaManager->listTableIndexes('akeneo_supplier_portal_product_file_imported_by_job_execution');
        $this->assertArrayHasKey('akeneo_supplier_portal_product_file_imported_execution_id_index', $index);
        $this->assertArrayHasKey('import_status', $tableColumns);
        $this->assertArrayNotHasKey('job_execution_result', $tableColumns);
        $this->assertTrue($tableColumns['import_status']->getNotnull());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
