<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_8_0_20230811162400_create_akeneo_workflow_entity_workflow_table extends AbstractMigration
{

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
            CREATE TABLE IF NOT EXISTS akeneo_workflow_entity_workflow (
                `workflow_uuid` binary(16) NOT NULL,
                `entity_uuid` binary(16) NOT NULL,
                `created` datetime NOT NULL,
                `status` varchar(255) NOT NULL DEFAULT 'todo',
                PRIMARY KEY (`workflow_uuid`, `entity_uuid`),
                CONSTRAINT FK_ENTITY_WORFLOW_workflow_uuid FOREIGN KEY (workflow_uuid) REFERENCES akeneo_workflow (uuid)
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
