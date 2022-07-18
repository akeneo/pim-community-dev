<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20220708095751_supplier_portal_add_supplier_file_table extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS `akeneo_supplier_portal_supplier_file` (
                `identifier` char(36) NOT NULL,
                `filename` varchar(255) NOT NULL,
                `path` varchar(255) NOT NULL,     
                `uploaded_by_contributor` varchar(36) DEFAULT NULL,
                `uploaded_by_supplier` varchar(36) NOT NULL,
                `uploaded_at` DATETIME NOT NULL,
                `downloaded` BOOLEAN NOT NULL DEFAULT false,
            PRIMARY KEY (`identifier`),
            CONSTRAINT `uploaded_by_supplier_foreign_key`
                FOREIGN KEY (`uploaded_by_supplier`)
                REFERENCES `akeneo_supplier_portal_supplier` (identifier)
                ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
