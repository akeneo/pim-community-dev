<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20220505000000_add_catalog_table extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
        CREATE TABLE IF NOT EXISTS akeneo_catalog (
            id BINARY(16) NOT NULL PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL
        );
        $this->addSql(<<<SQL
        CREATE TABLE IF NOT EXISTS akeneo_connectivity_connected_app_catalogs (
            catalog_id BINARY(16) NOT NULL,
            connected_app_id VARCHAR(36) NOT NULL,
            PRIMARY KEY (catalog_id, connected_app_id),
            INDEX (connected_app_id),
            FOREIGN KEY (catalog_id) REFERENCES akeneo_catalog(id) ON DELETE CASCADE,
            FOREIGN KEY (connected_app_id) REFERENCES akeneo_connectivity_connected_app(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
