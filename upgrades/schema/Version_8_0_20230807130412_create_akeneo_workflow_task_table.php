<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version_8_0_20230807130412_create_akeneo_workflow_task_table extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
            CREATE TABLE IF NOT EXISTS akeneo_workflow_task (
                uuid binary(16) PRIMARY KEY,
                translation json NOT NULL,
                configuration json NOT NULL,
                created DATETIME NOT NULL,
                updated DATETIME NOT NULL,
                deleted DATETIME DEFAULT NULL,
                workflow_uuid binary(16) NOT NULL,
                CONSTRAINT FK_workflow_task_workflow_uuid FOREIGN KEY (workflow_uuid) REFERENCES akeneo_workflow (uuid)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
