<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_6_0_20211119154203_add_is_stoppable_in_job_execution extends AbstractMigration
{
    private const STOPPABLE_JOB = [
        "csv_user_group_export",
        "xlsx_user_group_export",
        "csv_user_role_export",
        "xlsx_user_role_export",
        "csv_user_export",
        "xlsx_user_export",
        "csv_user_group_import",
        "xlsx_user_group_import",
        "csv_user_import",
        "csv_user_role_import",
        "xlsx_user_import",
        "xlsx_user_role_import",
        "csv_locale_import",
        "csv_locale_export",
        "xlsx_locale_import",
        "xlsx_locale_export",
        "csv_channel_import",
        "csv_channel_export",
        "xlsx_channel_import",
        "xlsx_channel_export",
        "csv_currency_import",
        "csv_currency_export",
        "xlsx_currency_import",
        "xlsx_currency_export",
        "compute_completeness_of_products_family",
        "compute_family_variant_structure_changes",
        "csv_group_import",
        "csv_group_export",
        "csv_product_import",
        "csv_product_model_import",
        "csv_product_export",
        "csv_product_model_export",
        "xlsx_product_import",
        "xlsx_product_model_import",
        "xlsx_product_export",
        "xlsx_product_model_export",
        "csv_category_import",
        "csv_category_export",
        "xlsx_category_import",
        "xlsx_category_export",
        "xlsx_group_import",
        "xlsx_group_export",
        "csv_product_quick_export",
        "csv_product_grid_context_quick_export",
        "xlsx_product_quick_export",
        "xlsx_product_grid_context_quick_export",
        "update_product_value",
        "add_product_value",
        "add_to_group",
        "remove_product_value",
        "move_to_category",
        "add_to_category",
        "remove_from_category",
        "add_association",
        "edit_common_attributes",
        "add_attribute_value",
        "add_to_existing_product_model",
        "convert_to_simple_products",
        "delete_products_and_product_models",
        "change_parent_product",
        "remove_non_existing_product_values",
        "csv_attribute_import",
        "csv_attribute_option_import",
        "csv_attribute_group_import",
        "csv_attribute_export",
        "csv_attribute_option_export",
        "csv_attribute_group_export",
        "xlsx_attribute_import",
        "xlsx_attribute_option_import",
        "xlsx_attribute_group_import",
        "xlsx_attribute_export",
        "xlsx_attribute_option_export",
        "xlsx_attribute_group_export",
        "csv_association_type_import",
        "csv_association_type_export",
        "xlsx_association_type_import",
        "xlsx_association_type_export",
        "csv_family_export",
        "csv_family_variant_export",
        "xlsx_family_export",
        "xlsx_family_variant_export",
        "csv_group_type_import",
        "csv_group_type_export",
        "xlsx_group_type_import",
        "xlsx_group_type_export",
        "set_attribute_requirements",
        "data_quality_insights_prepare_evaluations",
        "data_quality_insights_recompute_products_scores",
        "csv_table_attribute_options_export",
        "xlsx_table_attribute_options_export",
        "csv_table_attribute_options_import",
        "xlsx_table_attribute_options_import",
        "csv_product_import_with_rules",
        "csv_product_model_import_with_rules",
        "xlsx_product_import_with_rules",
        "xlsx_product_model_import_with_rules",
        "yml_rule_import",
        "yml_rule_export",
        "rule_impacted_product_count",
        "rule_engine_execute_rules",
        "csv_product_proposal_import",
        "csv_product_model_proposal_import",
        "csv_published_product_export",
        "xlsx_product_proposal_import",
        "xlsx_product_model_proposal_import",
        "xlsx_published_product_export",
        "approve_product_draft",
        "refuse_product_draft",
        "csv_published_product_quick_export",
        "csv_published_product_grid_context_quick_export",
        "xlsx_published_product_quick_export",
        "xlsx_published_product_grid_context_quick_export",
        "publish_product",
        "unpublish_product",
        "asset_manager_mass_delete_assets",
        "asset_manager_mass_edit_assets",
        "asset_manager_csv_asset_export",
        "asset_manager_xlsx_asset_export",
        "xlsx_tailored_product_export",
        "csv_tailored_product_export",
        "xlsx_tailored_product_model_export",
        "csv_tailored_product_model_export",
        "reference_entity_mass_delete_records",
    ];

    public function up(Schema $schema): void
    {
        if ($schema->getTable('akeneo_batch_job_execution')->hasColumn('is_stoppable')) {
            $this->write('is_stoppable column already exists in akeneo_batch_job_execution');

            return;
        }

        $this->addSql("ALTER TABLE akeneo_batch_job_execution ADD is_stoppable TINYINT(1) DEFAULT 0");
        $this->addSql("UPDATE akeneo_batch_job_execution SET is_stoppable = 1 WHERE job_instance_id IN (SELECT id FROM akeneo_batch_job_instance WHERE code IN ('"
            . implode("', '", self::STOPPABLE_JOB) . "'))");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
