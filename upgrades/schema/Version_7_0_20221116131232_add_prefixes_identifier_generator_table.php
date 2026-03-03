<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20221116131232_add_prefixes_identifier_generator_table extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql(<<<SQL
            CREATE TABLE IF NOT EXISTS pim_catalog_identifier_generator_prefixes (
                `product_uuid` binary(16) NOT NULL,
                `attribute_id` INT NOT NULL,
                `prefix` VARCHAR(255) NOT NULL,
                `number` INT NOT NULL,
                CONSTRAINT `FK_PRODUCTUUID` FOREIGN KEY (`product_uuid`) REFERENCES `pim_catalog_product` (`uuid`),
                CONSTRAINT `FK_ATTRIBUTEID` FOREIGN KEY (`attribute_id`) REFERENCES `pim_catalog_attribute` (`id`),
                INDEX index_identifier_generator_prefixes (`attribute_id`, `prefix`, `number`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL
        );
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
