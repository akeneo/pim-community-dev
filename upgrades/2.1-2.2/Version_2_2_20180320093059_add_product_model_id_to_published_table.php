<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Adds new columns related to the products with variants in the published table
 */
class Version_2_2_20180320093059_add_product_model_id_to_published_table extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $sql = <<<sql
        ALTER TABLE `pimee_workflow_published_product` 
        ADD `product_model_id` int(11) DEFAULT NULL;
        ALTER TABLE `pimee_workflow_published_product` 
        ADD CONSTRAINT `FK_E3566E69B2C5DD70` FOREIGN KEY (`product_model_id`) REFERENCES `pim_catalog_product_model` (id) ON DELETE CASCADE;
        
        ALTER TABLE `pimee_workflow_published_product` 
        ADD `family_variant_id` int(11) DEFAULT NULL;
        ALTER TABLE `pimee_workflow_published_product` 
        ADD CONSTRAINT `FK_E3566E698A37AD0` FOREIGN KEY (`family_variant_id`) REFERENCES `pim_catalog_family_variant` (id);

sql;
        $this->addSql($sql);
        $this->addSql('ALTER TABLE pimee_workflow_published_product RENAME INDEX fk_e3566e69b2c5dd70 TO IDX_E3566E69B2C5DD70');
        $this->addSql('ALTER TABLE pimee_workflow_published_product RENAME INDEX fk_e3566e698a37ad0 TO IDX_E3566E698A37AD0');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
