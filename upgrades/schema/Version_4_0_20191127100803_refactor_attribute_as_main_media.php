<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 *  Alter the `akeneo_asset_manager_asset_family` table renaming the following column (and its index):
 *  From   attribute_as_image
 *  To     attribute_as_main_media
 */
final class Version_4_0_20191127100803_refactor_attribute_as_main_media extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $alterTable = <<<SQL
ALTER TABLE `akeneo_asset_manager_asset_family`
    DROP FOREIGN KEY `akeneoasset_manager_attribute_as_image_foreign_key`,
    CHANGE `attribute_as_image` `attribute_as_main_media` VARCHAR(255),
    ADD CONSTRAINT `akeneoasset_manager_attribute_as_main_media_foreign_key`
        FOREIGN KEY (`attribute_as_main_media`)
        REFERENCES `akeneo_asset_manager_attribute` (`identifier`)
        ON DELETE SET NULL
SQL;

        $this->addSql($alterTable);
    }

    public function down(Schema $schema) : void
    {
        $alterTable = <<<SQL
ALTER TABLE `akeneo_asset_manager_asset_family`
    DROP FOREIGN KEY `akeneoasset_manager_attribute_as_main_media_foreign_key`,
    CHANGE `attribute_as_main_media` `attribute_as_image` VARCHAR(255),
    ADD CONSTRAINT `akeneoasset_manager_attribute_as_image_foreign_key`
        FOREIGN KEY (`attribute_as_image`)
        REFERENCES `akeneo_asset_manager_attribute` (`identifier`)
        ON DELETE SET NULL
SQL;

        $this->addSql($alterTable);
    }
}
