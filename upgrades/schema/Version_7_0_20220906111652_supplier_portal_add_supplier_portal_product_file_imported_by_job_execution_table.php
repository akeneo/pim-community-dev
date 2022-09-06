<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20220906111652_supplier_portal_add_supplier_portal_product_file_imported_by_job_execution_table extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS `akeneo_supplier_portal_product_file_imported_by_job_execution` (
                `id` bigint UNSIGNED AUTO_INCREMENT NOT NULL,
                `product_file_identifier` char(36) NOT NULL,
                `job_execution_id` int NOT NULL,
                `job_execution_result` varchar(100) NULL,
                `finished_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                CONSTRAINT `UC_product_file_imported_by_job_execution_job_execution_id` UNIQUE (`job_execution_id`),
                CONSTRAINT `file_imported_by_job_execution_product_file_identifier_fk`
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
