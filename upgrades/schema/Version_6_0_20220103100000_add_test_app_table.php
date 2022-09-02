<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_6_0_20220103100000_add_test_app_table extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
        CREATE TABLE IF NOT EXISTS akeneo_connectivity_test_app(
            client_id VARCHAR(36) NOT NULL PRIMARY KEY,
            client_secret VARCHAR(100) NOT NULL,
            name VARCHAR(255) NOT NULL,
            activate_url VARCHAR(255) NOT NULL,
            callback_url VARCHAR(255) NOT NULL,
            user_id INT DEFAULT NULL,
            CONSTRAINT `FK_TESTAPPUSERID` FOREIGN KEY (`user_id`) REFERENCES `oro_user` (`id`) ON DELETE SET NULL
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
