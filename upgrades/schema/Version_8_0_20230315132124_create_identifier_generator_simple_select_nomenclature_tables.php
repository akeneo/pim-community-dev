<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Create identifier generator simple select nomenclature tables
 */
final class Version_8_0_20230315132124_create_identifier_generator_simple_select_nomenclature_tables extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create identifier generator simple select nomenclature tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
            CREATE TABLE IF NOT EXISTS pim_catalog_identifier_generator_simple_select_nomenclature (
                `option_id` INT NOT NULL,
                `value` VARCHAR(255) NOT NULL,
                UNIQUE INDEX simple_select_nomenclature_option_id (`option_id`),
                CONSTRAINT `FK_SIMPLE_SELECT_NOMENCLATURE` FOREIGN KEY (`option_id`) REFERENCES `pim_catalog_attribute_option` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
