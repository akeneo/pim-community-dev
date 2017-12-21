<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version_2_0_20171221083904_add_delete_cascade_on_variant_attribute_set extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE pim_catalog_variant_attribute_set_has_attributes DROP FOREIGN KEY FK_E9C4264ABAAF4009');
        $this->addSql('ALTER TABLE pim_catalog_variant_attribute_set_has_attributes ADD CONSTRAINT FK_E9C4264ABAAF4009 FOREIGN KEY (attributes_id) REFERENCES pim_catalog_attribute (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE pim_catalog_variant_attribute_set_has_attributes DROP FOREIGN KEY FK_E9C4264ABAAF4009');
        $this->addSql('ALTER TABLE pim_catalog_variant_attribute_set_has_attributes ADD CONSTRAINT FK_E9C4264ABAAF4009 FOREIGN KEY (attributes_id) REFERENCES pim_catalog_attribute (id)');
    }
}
