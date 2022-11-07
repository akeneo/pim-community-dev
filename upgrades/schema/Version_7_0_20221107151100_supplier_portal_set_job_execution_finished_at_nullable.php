<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20221107151100_supplier_portal_set_job_execution_finished_at_nullable extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->skipIf($this->alreadyModified($schema), 'The finished_at column is already nullable.');

        $sql = <<<SQL
            ALTER TABLE akeneo_supplier_portal_product_file_imported_by_job_execution 
            MODIFY finished_at datetime NULL;
        SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function alreadyModified(Schema $schema): bool
    {
        $query = <<<SQL
            SELECT IS_NULLABLE FROM information_schema.`COLUMNS`
            WHERE TABLE_SCHEMA = :table_schema
            AND TABLE_NAME = 'akeneo_supplier_portal_product_file_imported_by_job_execution'
            AND COLUMN_NAME = 'finished_at';
        SQL;

        $result = $this->connection->executeQuery($query, [
            'table_schema' => $schema->getName(),
        ])->fetchOne();

        return $result === "YES";
    }
}
