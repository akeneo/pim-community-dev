<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_8_0_20230720154426_add_template_exported_by_job_execution_table extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS `akeneo_supplier_portal_template_exported_by_job_execution` (
                `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                `template_configuration_identifier` binary(16) NOT NULL,
                `job_execution_id` int NOT NULL,
                `finished_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `template_exported_by_job_execution_id_unique` (`job_execution_id`),
                KEY `template_exported_by_job_execution_id_index` (`job_execution_id`),
                INDEX `template_exported_by_job_execution_id_fk` (`template_configuration_identifier`),
                CONSTRAINT `template_exported_by_job_execution_id_fk` 
                    FOREIGN KEY (`template_configuration_identifier`) 
                        REFERENCES `akeneo_supplier_portal_template_configuration` (`identifier`) 
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
