<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Switch to full UTF8 support by using MySQL utf8mb4 instead of the incomplete MySQL utf8
 */
class Version_3_0_20180823184342_switch_to_utf8mb4_on_ee extends AbstractMigration
{
    private $tablesList = [
        "akeneo_rule_engine_rule_definition",
        "akeneo_rule_engine_rule_relation",
        "pimee_product_asset_asset",
        "pimee_product_asset_asset_category",
        "pimee_product_asset_asset_tag",
        "pimee_product_asset_category",
        "pimee_product_asset_category_translation",
        "pimee_product_asset_channel_variation_configuration",
        "pimee_product_asset_file_metadata",
        "pimee_product_asset_reference",
        "pimee_product_asset_tag",
        "pimee_product_asset_variation",
        "pimee_security_asset_category_access",
        "pimee_security_attribute_group_access",
        "pimee_security_job_profile_access",
        "pimee_security_locale_access",
        "pimee_security_product_category_access",
        "pimee_teamwork_assistant_completeness_per_attribute_group",
        "pimee_teamwork_assistant_project",
        "pimee_teamwork_assistant_project_product",
        "pimee_teamwork_assistant_project_status",
        "pimee_teamwork_assistant_project_user_group",
        "pimee_workflow_category_published_product",
        "pimee_workflow_group_published_product",
        "pimee_workflow_product_draft",
        "pimee_workflow_product_model_draft",
        "pimee_workflow_product_unique_data",
        "pimee_workflow_published_product",
        "pimee_workflow_published_product_association",
        "pimee_workflow_published_product_association_published_group",
        "pimee_workflow_published_product_association_published_product",
        "pimee_workflow_published_product_completeness",
        "pimee_workflow_published_product_completeness_missing_attribute"
    ];

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $dbName = $this->connection->getDatabase();

        foreach($this->tablesList as $tableName) {
            $this->addSql(
                sprintf('ALTER TABLE %s CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', $tableName)
            );
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
