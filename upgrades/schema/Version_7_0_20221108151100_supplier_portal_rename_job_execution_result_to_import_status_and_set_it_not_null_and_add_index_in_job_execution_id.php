<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20221108151100_supplier_portal_rename_job_execution_result_to_import_status_and_set_it_not_null_and_add_index_in_job_execution_id extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->skipIf($this->alreadyModified($schema), 'The import_status column already exists.');

        $sql = <<<SQL
            ALTER TABLE akeneo_supplier_portal_product_file_imported_by_job_execution 
            MODIFY job_execution_result VARCHAR(100) NOT NULL;
        SQL;

        $this->addSql($sql);

        $sql = <<<SQL
            ALTER TABLE akeneo_supplier_portal_product_file_imported_by_job_execution 
            RENAME COLUMN job_execution_result TO import_status;
        SQL;

        $this->addSql($sql);

        $this->addSql(
            "CREATE INDEX akeneo_supplier_portal_product_file_imported_execution_id_index 
                    ON akeneo_supplier_portal_product_file_imported_by_job_execution(job_execution_id)"
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function alreadyModified(Schema $schema): bool
    {
        $query = <<<SQL
            SELECT 1 FROM information_schema.`COLUMNS`
            WHERE TABLE_SCHEMA = :table_schema
            AND TABLE_NAME = 'akeneo_supplier_portal_product_file_imported_by_job_execution'
            AND COLUMN_NAME = 'import_status';
        SQL;

        $result = $this->connection->executeQuery($query, [
            'table_schema' => $schema->getName(),
        ])->fetchOne();

        return $result !== false;
    }
}
