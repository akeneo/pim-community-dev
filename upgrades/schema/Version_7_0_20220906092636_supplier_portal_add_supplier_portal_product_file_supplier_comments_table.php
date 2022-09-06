<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20220906092636_supplier_portal_add_supplier_portal_product_file_supplier_comments_table extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS `akeneo_supplier_portal_product_file_supplier_comments` (
                `id` bigint UNSIGNED AUTO_INCREMENT NOT NULL,
                `author_email` varchar(255) NOT NULL,
                `product_file_identifier` char(36) NOT NULL,
                `content` varchar(255) NOT NULL,
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                CONSTRAINT `product_file_supplier_comments_product_file_identifier_fk`
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
