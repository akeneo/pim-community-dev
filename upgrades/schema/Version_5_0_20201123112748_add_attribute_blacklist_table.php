<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_5_0_20201123112748_add_attribute_blacklist_table extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS pim_catalog_attribute_blacklist (
                `attribute_code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL PRIMARY KEY,
                `cleanup_job_execution_id` INTEGER DEFAULT NULL,
                UNIQUE KEY `searchunique_idx` (`attribute_code`),
                CONSTRAINT `FK_BDE7D0925812C06B` FOREIGN KEY (`cleanup_job_execution_id`) REFERENCES `akeneo_batch_job_execution` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            SQL;
        $this->addSql($sql);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
