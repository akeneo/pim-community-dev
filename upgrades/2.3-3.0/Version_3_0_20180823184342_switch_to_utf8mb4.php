<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Switch to full UTF8 support by using MySQL utf8mb4 instead of the incomplete MySQL utf8
 */
class Version_3_0_20180823184342_switch_to_utf8mb4 extends AbstractMigration
{
    private $tablesList = [
        "acl_classes",
        "acl_entries",
        "acl_object_identities",
        "acl_object_identity_ancestors",
        "acl_security_identities",
        "akeneo_batch_job_execution",
        "akeneo_batch_job_execution_queue",
        "akeneo_batch_job_instance",
        "akeneo_batch_step_execution",
        "akeneo_batch_warning",
        "akeneo_file_storage_file_info",
        "akeneo_structure_version_last_update",
        "oro_access_group",
        "oro_access_role",
        "oro_config",
        "oro_config_value",
        "oro_user",
        "oro_user_access_group",
        "oro_user_access_group_role",
        "oro_user_access_role",
        "pim_aggregated_volume",
        "pim_api_access_token",
        "pim_api_auth_code",
        "pim_api_client",
        "pim_api_refresh_token",
        "pim_catalog_association",
        "pim_catalog_association_group",
        "pim_catalog_association_product",
        "pim_catalog_association_product_model",
        "pim_catalog_association_product_model_to_group",
        "pim_catalog_association_product_model_to_product",
        "pim_catalog_association_product_model_to_product_model",
        "pim_catalog_association_type",
        "pim_catalog_association_type_translation",
        "pim_catalog_attribute",
        "pim_catalog_attribute_group",
        "pim_catalog_attribute_group_translation",
        "pim_catalog_attribute_locale",
        "pim_catalog_attribute_option",
        "pim_catalog_attribute_option_value",
        "pim_catalog_attribute_requirement",
        "pim_catalog_attribute_translation",
        "pim_catalog_category",
        "pim_catalog_category_product",
        "pim_catalog_category_product_model",
        "pim_catalog_category_translation",
        "pim_catalog_channel",
        "pim_catalog_channel_currency",
        "pim_catalog_channel_locale",
        "pim_catalog_channel_translation",
        "pim_catalog_completeness",
        "pim_catalog_completeness_missing_attribute",
        "pim_catalog_currency",
        "pim_catalog_family",
        "pim_catalog_family_attribute",
        "pim_catalog_family_translation",
        "pim_catalog_family_variant",
        "pim_catalog_family_variant_attribute_set",
        "pim_catalog_family_variant_has_variant_attribute_sets",
        "pim_catalog_family_variant_translation",
        "pim_catalog_group",
        "pim_catalog_group_product",
        "pim_catalog_group_translation",
        "pim_catalog_group_type",
        "pim_catalog_group_type_translation",
        "pim_catalog_locale",
        "pim_catalog_product",
        "pim_catalog_product_model",
        "pim_catalog_product_model_association",
        "pim_catalog_product_unique_data",
        "pim_catalog_variant_attribute_set_has_attributes",
        "pim_catalog_variant_attribute_set_has_axes",
        "pim_comment_comment",
        "pim_datagrid_view",
        "pim_notification_notification",
        "pim_notification_user_notification",
        "pim_user_default_datagrid_view",
        "pim_versioning_version"
    ];

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $dbName = $this->connection->getDatabase();

        $this->addSql(
            sprintf('ALTER DATABASE %s CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci', $dbName)
        );

        foreach($this->tablesList as $tableName) {
            $this->addSql(
                sprintf('ALTER TABLE %s CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', $tableName)
            );
        }

        $this->addSql('ALTER TABLE pim_session CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
