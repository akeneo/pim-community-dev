<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * This migration adds the Syndication platform table
 */
final class Version_7_0_20220408071200_create_connected_channel_table extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS `akeneo_syndication_connected_channel` (
                `code` VARCHAR(255) NOT NULL,
                `label` VARCHAR(255) NOT NULL,
                `enabled` BOOLEAN NOT NULL,
                PRIMARY KEY (`code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS `akeneo_syndication_family` (
                `code` VARCHAR(255) NOT NULL,
                `connected_channel_code` VARCHAR(255) NOT NULL,
                `label` VARCHAR(255) NOT NULL,
                `data` JSON NOT NULL,
                PRIMARY KEY (`code`, `connected_channel_code`),
                UNIQUE akeneo_syndication_family_code_family_ux (connected_channel_code, code),
                CONSTRAINT akeneo_syndication_connected_channel_code_foreign_key FOREIGN KEY (connected_channel_code) REFERENCES akeneo_syndication_connected_channel (code)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
