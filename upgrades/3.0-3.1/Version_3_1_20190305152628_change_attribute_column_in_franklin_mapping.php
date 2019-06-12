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
        // find foreign key 
        $selectForeignKey = <<<'SQL'
SELECT TC.CONSTRAINT_NAME
FROM information_schema.TABLE_CONSTRAINTS TC
WHERE TC.CONSTRAINT_TYPE = 'FOREIGN KEY'
AND TC.TABLE_SCHEMA = 'akeneo_pim'
AND TC.TABLE_NAME = 'pimee_franklin_insights_identifier_mapping'
SQL;
        $stmt = $this->connection->executeQuery($selectForeignKey);
        $foreignKeyName = $stmt->fetchColumn();
        
        // find unique key
        $selectUniqueKey = <<<'SQL'
SELECT TC.CONSTRAINT_NAME
FROM information_schema.TABLE_CONSTRAINTS TC
WHERE TC.CONSTRAINT_TYPE = 'UNIQUE'
AND TC.TABLE_SCHEMA = 'akeneo_pim'
AND TC.TABLE_NAME = 'pimee_franklin_insights_identifier_mapping'
SQL;
        $stmt = $this->connection->executeQuery($selectUniqueKey);
        $uniqueKeyName = $stmt->fetchColumn();

        // update attribute code
        $updateAttributeCodeQuery = <<<SQL
UPDATE pimee_franklin_insights_identifier_mapping
SET attribute_code = (
  SELECT attribute.code FROM pim_catalog_attribute attribute WHERE attribute.id = attribute_id
)
WHERE attribute_id IS NOT NULL;
SQL;

        if ($foreignKeyName !== false) {
            $this->addSql('ALTER TABLE pimee_franklin_insights_identifier_mapping DROP FOREIGN KEY ' . $foreignKeyName);
        }
        if ($uniqueKeyName !== false) {
            $this->addSql('DROP INDEX ' . $uniqueKeyName . ' ON pimee_franklin_insights_identifier_mapping');
        }
        $this->addSql('ALTER TABLE pimee_franklin_insights_identifier_mapping ADD attribute_code VARCHAR(255) DEFAULT NULL AFTER attribute_id');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_attribute_code ON pimee_franklin_insights_identifier_mapping (attribute_code)');
        $this->addSql($updateAttributeCodeQuery);
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
