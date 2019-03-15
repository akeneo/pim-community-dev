<?php

namespace Pim\Upgrade\schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version_3_1_20190305152628_change_attribute_column_in_franklin_mapping extends AbstractMigration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE pimee_franklin_insights_identifier_mapping DROP FOREIGN KEY FK_5F1E2B0DB6E62EFA');
        $this->addSql('ALTER TABLE pimee_franklin_insights_identifier_mapping ADD attribute_code VARCHAR(255) DEFAULT NULL AFTER attribute_id');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_attribute_code ON pimee_franklin_insights_identifier_mapping (attribute_code)');

        $updateAttributeCodeQuery = <<<SQL
UPDATE pimee_franklin_insights_identifier_mapping
SET attribute_code = (
  SELECT attribute.code FROM pim_catalog_attribute attribute WHERE attribute.id = attribute_id
)
WHERE attribute_id IS NOT NULL;
SQL;

        $this->addSql($updateAttributeCodeQuery);
        $this->addSql('DROP INDEX UNIQ_5F1E2B0DB6E62EFA ON pimee_franklin_insights_identifier_mapping');
        $this->addSql('ALTER TABLE pimee_franklin_insights_identifier_mapping DROP attribute_id');
    }

    /**
     * {@inheritdoc}
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
