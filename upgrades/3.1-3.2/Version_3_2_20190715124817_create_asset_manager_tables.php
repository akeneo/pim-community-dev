<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Create the schema to support asset manager.
 */
class Version_3_2_20190715124817_create_asset_manager_tables extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $createSchema = <<<SQL
SET foreign_key_checks = 0;

CREATE TABLE `akeneo_asset_manager_asset_family` (
    `identifier` VARCHAR(255) NOT NULL,
    `labels` JSON NOT NULL,
    `image` VARCHAR(255) NULL,
    `attribute_as_label` VARCHAR(255) NULL,
    `attribute_as_image` VARCHAR(255) NULL,
    `rule_templates` JSON NOT NULL,
    PRIMARY KEY (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE akeneo_asset_manager_asset (
    identifier VARCHAR(255) NOT NULL,
    code VARCHAR(255) NOT NULL,
    asset_family_identifier VARCHAR(255) NOT NULL,
    value_collection JSON NOT NULL,
    PRIMARY KEY (identifier),
    UNIQUE akeneoasset_manager_identifier_asset_ux (asset_family_identifier, code),
    CONSTRAINT akeneoasset_manager_asset_family_identifier_foreign_key FOREIGN KEY (asset_family_identifier) REFERENCES akeneo_asset_manager_asset_family (identifier)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `akeneo_asset_manager_attribute` (
    `identifier` VARCHAR(255) NOT NULL,
    `code` VARCHAR(255) NOT NULL,
    `asset_family_identifier` VARCHAR(255) NOT NULL,
    `labels` JSON NOT NULL,
    `attribute_type` VARCHAR(255) NOT NULL,
    `attribute_order` INT NOT NULL,
    `is_required` BOOLEAN NOT NULL,
    `value_per_channel` BOOLEAN NOT NULL,
    `value_per_locale` BOOLEAN NOT NULL,
    `additional_properties` JSON NOT NULL,
    PRIMARY KEY (`identifier`),
    UNIQUE `attribute_identifier_index` (`code`, `asset_family_identifier`),
    UNIQUE `attribute_asset_family_order_index` (`asset_family_identifier`, `attribute_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `akeneo_asset_manager_asset_family_permissions` (
    `asset_family_identifier` VARCHAR(255) NOT NULL,
    `user_group_identifier` SMALLINT(6) NOT NULL,
    `right_level` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`asset_family_identifier`, `user_group_identifier`),
    CONSTRAINT permissions_asset_family_identifier_foreign_key FOREIGN KEY (`asset_family_identifier`) REFERENCES `akeneo_asset_manager_asset_family` (identifier)
      ON DELETE CASCADE,
    CONSTRAINT asset_manager_user_group_foreign_key FOREIGN KEY (`user_group_identifier`) REFERENCES `oro_access_group` (id)
      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `akeneo_asset_manager_asset_family`
    ADD CONSTRAINT akeneoasset_manager_attribute_as_label_foreign_key
    FOREIGN KEY (attribute_as_label)
    REFERENCES akeneo_asset_manager_attribute (identifier)
    ON DELETE SET NULL;
    
ALTER TABLE `akeneo_asset_manager_asset_family`
    ADD CONSTRAINT akeneoasset_manager_attribute_as_image_foreign_key
    FOREIGN KEY (attribute_as_image)
    REFERENCES akeneo_asset_manager_attribute (identifier)
    ON DELETE SET NULL;
    
ALTER TABLE `akeneo_asset_manager_attribute`
    ADD CONSTRAINT attribute_asset_family_identifier_foreign_key
    FOREIGN KEY (`asset_family_identifier`)
    REFERENCES `akeneo_asset_manager_asset_family` (identifier)
    ON DELETE CASCADE;

SET foreign_key_checks = 1;
SQL;
        $this->addSql($createSchema);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
