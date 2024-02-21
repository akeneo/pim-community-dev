<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version_8_0_20230120154546_add_identifier_generator_sequence_table extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'This migration adds the "pim_catalog_identifier_generator_sequence" table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
            CREATE TABLE IF NOT EXISTS pim_catalog_identifier_generator_sequence (
                `attribute_id` INT NOT NULL,
                `identifier_generator_uuid` binary(16) NOT NULL,
                `prefix` VARCHAR(255) NOT NULL,
                `last_allocated_number` BIGINT UNSIGNED NOT NULL,
                CONSTRAINT `FK_SEQ_ATTRIBUTEID` FOREIGN KEY (`attribute_id`) REFERENCES `pim_catalog_attribute` (`id`) ON DELETE CASCADE,
                CONSTRAINT `FK_SEQ_IDENTIFIERGENERATORUUID` FOREIGN KEY (`identifier_generator_uuid`) REFERENCES `pim_catalog_identifier_generator` (`uuid`) ON DELETE CASCADE,
                UNIQUE INDEX sequence_attribute_identifier_prefix (attribute_id, identifier_generator_uuid, prefix),
                INDEX index_identifier_generator_sequence (`attribute_id`, `identifier_generator_uuid`, `prefix`, `last_allocated_number`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
