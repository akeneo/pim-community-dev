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
            owner_id INT NOT NULL,
            created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_owner (owner_id),
            CONSTRAINT fk_owner FOREIGN KEY (owner_id) REFERENCES oro_user(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
