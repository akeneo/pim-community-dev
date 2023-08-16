<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_8_0_20230811162401_create_akeneo_workflow_entity_task_table extends AbstractMigration
{

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
            CREATE TABLE IF NOT EXISTS akeneo_workflow_entity_task (
                `task_uuid` binary(16) NOT NULL,
                `entity_uuid` binary(16) NOT NULL,
                `assignee` json NOT NULL,
                PRIMARY KEY (`task_uuid`, `entity_uuid`),
                CONSTRAINT FK_ENTITY_TASK_task_uuid FOREIGN KEY (task_uuid) REFERENCES akeneo_workflow_task (uuid)
                ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
