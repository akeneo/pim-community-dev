<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add CASCADE DELETE on pim_catalog_family_variant_attribute_set related foreign keys
 */
class Version_2_2_20180523125419 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE pim_catalog_family_variant_has_variant_attribute_sets DROP FOREIGN KEY FK_1F4DC7028A37AD0');
        $this->addSql('ALTER TABLE pim_catalog_family_variant_has_variant_attribute_sets DROP FOREIGN KEY FK_1F4DC702D8404D');
        $this->addSql('ALTER TABLE pim_catalog_family_variant_has_variant_attribute_sets ADD CONSTRAINT FK_1F4DC7028A37AD0 FOREIGN KEY (family_variant_id) REFERENCES pim_catalog_family_variant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pim_catalog_family_variant_has_variant_attribute_sets ADD CONSTRAINT FK_1F4DC702D8404D FOREIGN KEY (variant_attribute_sets_id) REFERENCES pim_catalog_family_variant_attribute_set (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pim_catalog_variant_attribute_set_has_attributes DROP FOREIGN KEY FK_E9C4264A11D06F0E');
        $this->addSql('ALTER TABLE pim_catalog_variant_attribute_set_has_attributes ADD CONSTRAINT FK_E9C4264A11D06F0E FOREIGN KEY (variant_attribute_set_id) REFERENCES pim_catalog_family_variant_attribute_set (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pim_catalog_variant_attribute_set_has_axes DROP FOREIGN KEY FK_6965051E11D06F0E');
        $this->addSql('ALTER TABLE pim_catalog_variant_attribute_set_has_axes ADD CONSTRAINT FK_6965051E11D06F0E FOREIGN KEY (variant_attribute_set_id) REFERENCES pim_catalog_family_variant_attribute_set (id) ON DELETE CASCADE');

        // Delete orphan variant attribute sets
        $sql = <<<SQL
DELETE v.* FROM `pim_catalog_family_variant_attribute_set` v
WHERE v.id NOT IN (
  SELECT `variant_attribute_sets_id`
  FROM `pim_catalog_family_variant_has_variant_attribute_sets`
);
SQL;
        $this->addSql($sql);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
