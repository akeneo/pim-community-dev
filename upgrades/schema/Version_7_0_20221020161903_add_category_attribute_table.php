<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20221020161903_add_category_attribute_table extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql(<<<SQL
            CREATE TABLE IF NOT EXISTS `pim_catalog_category_attribute` (
                `uuid` BINARY(16) PRIMARY KEY,
                `code` VARCHAR(100) NOT NULL,  
                `category_template_uuid` binary(16) NOT NULL,
                `labels` JSON NULL,
                `attribute_type` VARCHAR(100) NOT NULL,
                `attribute_order` INT NOT NULL,
                `is_required` TINYINT(1) NOT NULL,
                `is_scopable` TINYINT(1) NOT NULL,
                `is_localizable` TINYINT(1) NOT NULL,
                `additional_properties` JSON NULL,
                CONSTRAINT `FK_ATTRIBUTE_template_uiid` FOREIGN KEY (`category_template_uuid`) REFERENCES `pim_catalog_category_template` (`uuid`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
            SQL
        );
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
