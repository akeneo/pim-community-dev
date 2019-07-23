<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Rename the rule_templates field to product_link_rules in the akeneo_asset_manager_asset_family table
 */
class Version_3_2_20190723093630_rename_rule_templates_field extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE akeneo_asset_manager_asset_family CHANGE rule_templates product_link_rules JSON;');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE akeneo_asset_manager_asset_family CHANGE product_link_rules rule_templates JSON;');
    }
}
