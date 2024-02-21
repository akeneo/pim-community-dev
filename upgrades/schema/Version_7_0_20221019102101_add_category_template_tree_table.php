<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20221019102101_add_category_template_tree_table extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql(<<<SQL
            CREATE TABLE IF NOT EXISTS `pim_catalog_category_tree_template` (
                `category_tree_id` int NOT NULL,  
                `category_template_uuid` binary(16) NOT NULL,
                CONSTRAINT `FK_TREE_TEMPLATE_template_uuid` FOREIGN KEY (`category_template_uuid`) REFERENCES `pim_catalog_category_template` (`uuid`),
                CONSTRAINT `FK_TREE_TEMPLATE_tree_id` FOREIGN KEY (`category_tree_id`) REFERENCES `pim_catalog_category` (`id`),
                CONSTRAINT `PRIMARY` PRIMARY KEY (category_tree_id,category_template_uuid)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
            SQL
        );
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
