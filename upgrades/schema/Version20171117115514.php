<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171117115514 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pim_catalog_product_model DROP FOREIGN KEY FK_5943911E727ACA70');
        $this->addSql('ALTER TABLE pim_catalog_product_model ADD CONSTRAINT FK_5943911E727ACA70 FOREIGN KEY (parent_id) REFERENCES pim_catalog_product_model (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pim_catalog_product DROP FOREIGN KEY FK_91CD19C0B2C5DD70');
        $this->addSql('ALTER TABLE pim_catalog_product ADD CONSTRAINT FK_91CD19C0B2C5DD70 FOREIGN KEY (product_model_id) REFERENCES pim_catalog_product_model (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pim_catalog_product DROP FOREIGN KEY FK_91CD19C0B2C5DD70');
        $this->addSql('ALTER TABLE pim_catalog_product ADD CONSTRAINT FK_91CD19C0B2C5DD70 FOREIGN KEY (product_model_id) REFERENCES pim_catalog_product_model (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE pim_catalog_product_model DROP FOREIGN KEY FK_5943911E727ACA70');
        $this->addSql('ALTER TABLE pim_catalog_product_model ADD CONSTRAINT FK_5943911E727ACA70 FOREIGN KEY (parent_id) REFERENCES pim_catalog_product_model (id) ON DELETE SET NULL');
    }
}
