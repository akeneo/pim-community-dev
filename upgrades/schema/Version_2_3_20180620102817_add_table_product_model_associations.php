<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add tables needed to support the product model association feature
 */
class Version_2_3_20180620102817_add_table_product_model_associations extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE pim_catalog_product_model_association (id INT AUTO_INCREMENT NOT NULL, association_type_id INT NOT NULL, owner_id INT NOT NULL, INDEX IDX_F5F4C8CAB1E1C39 (association_type_id), INDEX IDX_F5F4C8CA7E3C61F9 (owner_id), UNIQUE INDEX locale_foreign_key_idx (owner_id, association_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pim_catalog_association_product_model_to_group (association_id INT NOT NULL, group_id INT NOT NULL, INDEX IDX_16EA55AEEFB9C8A5 (association_id), INDEX IDX_16EA55AEFE54D947 (group_id), PRIMARY KEY(association_id, group_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pim_catalog_association_product_model_to_product (association_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_3FF3ED19EFB9C8A5 (association_id), INDEX IDX_3FF3ED194584665A (product_id), PRIMARY KEY(association_id, product_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pim_catalog_association_product_model_to_product_model (association_id INT NOT NULL, product_model_id INT NOT NULL, INDEX IDX_12D4D59CEFB9C8A5 (association_id), INDEX IDX_12D4D59CB2C5DD70 (product_model_id), PRIMARY KEY(association_id, product_model_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pim_catalog_product_model_association ADD CONSTRAINT FK_F5F4C8CAB1E1C39 FOREIGN KEY (association_type_id) REFERENCES pim_catalog_association_type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pim_catalog_product_model_association ADD CONSTRAINT FK_F5F4C8CA7E3C61F9 FOREIGN KEY (owner_id) REFERENCES pim_catalog_product_model (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pim_catalog_association_product_model_to_group ADD CONSTRAINT FK_16EA55AEEFB9C8A5 FOREIGN KEY (association_id) REFERENCES pim_catalog_product_model_association (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pim_catalog_association_product_model_to_group ADD CONSTRAINT FK_16EA55AEFE54D947 FOREIGN KEY (group_id) REFERENCES pim_catalog_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pim_catalog_association_product_model_to_product ADD CONSTRAINT FK_3FF3ED19EFB9C8A5 FOREIGN KEY (association_id) REFERENCES pim_catalog_product_model_association (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pim_catalog_association_product_model_to_product ADD CONSTRAINT FK_3FF3ED194584665A FOREIGN KEY (product_id) REFERENCES pim_catalog_product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pim_catalog_association_product_model_to_product_model ADD CONSTRAINT FK_12D4D59CEFB9C8A5 FOREIGN KEY (association_id) REFERENCES pim_catalog_product_model_association (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pim_catalog_association_product_model_to_product_model ADD CONSTRAINT FK_12D4D59CB2C5DD70 FOREIGN KEY (product_model_id) REFERENCES pim_catalog_product_model (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
