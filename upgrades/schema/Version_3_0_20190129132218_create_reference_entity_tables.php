<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Creates the schema to support the reference entities.
 */
class Version_3_0_20190129132218_create_reference_entity_tables extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        $createSchema = <<<SQL
SET foreign_key_checks = 0;

CREATE TABLE `akeneo_reference_entity_reference_entity` (
    `identifier` VARCHAR(255) NOT NULL,
    `labels` JSON NOT NULL,
    `image` VARCHAR(255) NULL,
    `attribute_as_label` VARCHAR(255) NULL,
    `attribute_as_image` VARCHAR(255) NULL,
    PRIMARY KEY (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE akeneo_reference_entity_record (
    identifier VARCHAR(255) NOT NULL,
    code VARCHAR(255) NOT NULL,
    reference_entity_identifier VARCHAR(255) NOT NULL,
    value_collection JSON NOT NULL,
    PRIMARY KEY (identifier),
    UNIQUE akeneoreference_entity_identifier_record_ux (reference_entity_identifier, code),
    CONSTRAINT akeneoreference_entity_reference_entity_identifier_foreign_key FOREIGN KEY (reference_entity_identifier) REFERENCES akeneo_reference_entity_reference_entity (identifier)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `akeneo_reference_entity_attribute` (
    `identifier` VARCHAR(255) NOT NULL,
    `code` VARCHAR(255) NOT NULL,
    `reference_entity_identifier` VARCHAR(255) NOT NULL,
    `labels` JSON NOT NULL,
    `attribute_type` VARCHAR(255) NOT NULL,
    `attribute_order` INT NOT NULL,
    `is_required` BOOLEAN NOT NULL,
    `value_per_channel` BOOLEAN NOT NULL,
    `value_per_locale` BOOLEAN NOT NULL,
    `additional_properties` JSON NOT NULL,
    PRIMARY KEY (`identifier`),
    UNIQUE `attribute_identifier_index` (`code`, `reference_entity_identifier`),
    UNIQUE `attribute_reference_entity_order_index` (`reference_entity_identifier`, `attribute_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `akeneo_reference_entity_reference_entity_permissions` (
    `reference_entity_identifier` VARCHAR(255) NOT NULL,
    `user_group_identifier` SMALLINT(6) NOT NULL,
    `right_level` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`reference_entity_identifier`, `user_group_identifier`),
    CONSTRAINT permissions_reference_entity_identifier_foreign_key FOREIGN KEY (`reference_entity_identifier`) REFERENCES `akeneo_reference_entity_reference_entity` (identifier)
      ON DELETE CASCADE,
    CONSTRAINT user_group_foreign_key FOREIGN KEY (`user_group_identifier`) REFERENCES `oro_access_group` (id)
      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `akeneo_reference_entity_reference_entity`
    ADD CONSTRAINT akeneoreference_entity_attribute_as_label_foreign_key
    FOREIGN KEY (attribute_as_label)
    REFERENCES akeneo_reference_entity_attribute (identifier)
    ON DELETE SET NULL;

ALTER TABLE `akeneo_reference_entity_reference_entity`
    ADD CONSTRAINT akeneoreference_entity_attribute_as_image_foreign_key
    FOREIGN KEY (attribute_as_image)
    REFERENCES akeneo_reference_entity_attribute (identifier)
    ON DELETE SET NULL;

ALTER TABLE `akeneo_reference_entity_attribute`
    ADD CONSTRAINT attribute_reference_entity_identifier_foreign_key
    FOREIGN KEY (`reference_entity_identifier`)
    REFERENCES `akeneo_reference_entity_reference_entity` (identifier)
    ON DELETE CASCADE;

SET foreign_key_checks = 1;
SQL;
        $this->addSql($createSchema);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
