<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Creates the new pim_configuration table.
 */
class Version_3_0_20181205151719_add_configuration_table extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            'mysql' !== $this->connection->getDatabasePlatform()->getName(),
            'Migration can only be executed safely on \'mysql\'.'
        );

        if (!$this->tableAlreadyExists()) {
            $this->createTable();
        }
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            'mysql' !== $this->connection->getDatabasePlatform()->getName(),
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql('DROP TABLE pim_configuration;');
    }

    private function tableAlreadyExists(): bool
    {
        $stmt = $this->connection->executeQuery('SHOW TABLES LIKE \'pim_configuration\';');

        return 1 <= $stmt->rowCount();
    }

    private function createTable(): void
    {
        $this->addSql(
            'CREATE TABLE pim_configuration (
                `code` VARCHAR(128) NOT NULL PRIMARY KEY,
                `values` JSON NOT NULL
            ) COLLATE utf8mb4_unicode_ci, ENGINE = InnoDB'
        );
    }
}
