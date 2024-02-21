<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Create identifier generator family nomenclature tables
 */
final class Version_8_0_20230131125133_create_identifier_generator_family_nomenclature_tables extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create identifier generator family nomenclature tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
            CREATE TABLE IF NOT EXISTS pim_catalog_identifier_generator_nomenclature_definition (
                `property_code` VARCHAR(255) NOT NULL,
                `definition` JSON NOT NULL DEFAULT ('{}'),
                UNIQUE INDEX nomenclature_definition_property_code (`property_code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL
        );
        $this->addSql(<<<SQL
            CREATE TABLE IF NOT EXISTS pim_catalog_identifier_generator_family_nomenclature (
                `family_id` INT NOT NULL,
                `value` VARCHAR(255) NOT NULL,
                UNIQUE INDEX family_nomenclature_family_id (`family_id`),
                CONSTRAINT `FK_FAMILY_NOMENCLATURE` FOREIGN KEY (`family_id`) REFERENCES `pim_catalog_family` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
