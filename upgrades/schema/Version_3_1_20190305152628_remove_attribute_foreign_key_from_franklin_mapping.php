<?php

namespace Pim\Upgrade\schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version_3_1_20190305152628_remove_attribute_foreign_key_from_franklin_mapping extends AbstractMigration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema)
    {
        $sql = <<<SQL
ALTER TABLE pimee_franklin_insights_identifier_mapping DROP FOREIGN KEY FK_5F1E2B0DB6E62EFA;
SQL;
        $this->addSql($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
