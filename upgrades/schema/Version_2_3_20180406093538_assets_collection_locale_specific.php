<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Migrating asset collections created with available locales
 */
class Version_2_3_20180406093538_assets_collection_locale_specific extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $sql = <<<SQL
      DELETE FROM pim_catalog_attribute_locale WHERE attribute_id IN (SELECT id FROM pim_catalog_attribute WHERE attribute_type = 'pim_assets_collection');
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
