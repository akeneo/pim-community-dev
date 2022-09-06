<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20220906110740_supplier_portal_add_supplier_portal_product_file_comments_read_by_supplier_table extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS `akeneo_supplier_portal_product_file_comments_read_by_supplier` (
                `id` bigint UNSIGNED AUTO_INCREMENT NOT NULL,
                `product_file_identifier` char(36) NOT NULL,
                `last_read_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                CONSTRAINT `UC_comments_read_by_supplier_product_file_identifier` UNIQUE (`product_file_identifier`),
                CONSTRAINT `comments_read_by_supplier_product_file_identifier_fk`
                    FOREIGN KEY (`product_file_identifier`)
                    REFERENCES `akeneo_supplier_portal_supplier_product_file` (identifier)
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
