<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @todo @pull-up Do not pull-up this migration in master/6.0 (cf PIM-10179)
 */
final class Version_5_0_20211125112429_remove_datagrid_view_unique_label extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->disableMigrationWarning();

        $databaseName = $this->connection->getParams()['dbname'];

        $findConstraintNameSql = <<< SQL
        SELECT DISTINCT CONSTRAINT_NAME
        FROM information_schema.TABLE_CONSTRAINTS
        WHERE table_name = 'pim_datagrid_view' AND constraint_type = 'UNIQUE' AND TABLE_SCHEMA = :database_name;
        SQL;

        $uniqueConstraintName = $this->connection->executeQuery($findConstraintNameSql, [
            'database_name' => $databaseName,
        ])->fetch(FetchMode::COLUMN);

        if (!is_string($uniqueConstraintName)) {
            return;
        }

        $dropIndexSql = sprintf('ALTER TABLE pim_datagrid_view DROP index %s', $uniqueConstraintName);
        $this->connection->executeQuery($dropIndexSql, ['index_name' => $uniqueConstraintName]);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function disableMigrationWarning(): void
    {
        $this->addSql('SELECT 1');
    }
}
