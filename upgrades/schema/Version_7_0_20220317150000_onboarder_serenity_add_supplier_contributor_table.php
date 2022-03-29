<?php

declare(strict_types=1);

namespace Pim\Upgrade\schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * This migration adds the Onboarder Serenity supplier table
 */
final class Version_7_0_20220317150000_onboarder_serenity_add_supplier_contributor_table extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS `akeneo_onboarder_serenity_supplier_contributor` (
              `id` bigint UNSIGNED AUTO_INCREMENT NOT NULL,
              `email` varchar(255) NOT NULL,
              `supplier_identifier` char(36) NOT NULL,
              PRIMARY KEY (`id`),
              CONSTRAINT UC_supplier_contributor_email UNIQUE (`email`),
              CONSTRAINT `supplier_identifier_foreign_key`
                FOREIGN KEY (`supplier_identifier`)
                REFERENCES `akeneo_onboarder_serenity_supplier` (identifier)
                ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
