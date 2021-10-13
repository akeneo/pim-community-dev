const {
  tools: {answerJson},
} = require('../test-helpers.js');

const listAcls = function (page, acls) {
  page.on('request', request => {
    if ('http://pim.com/rest/security/' === request.url()) {
      answerJson(request, acls, 200);
    }
  });
};

const grantAllAclsExcept = (page, exclude) => {
  const aclsToGrant = Object.keys(allAcls).reduce((aclsToGrant, aclCode) => {
    aclsToGrant[aclCode] = !exclude.includes(aclCode);

    return aclsToGrant;
  }, {});

  listAcls(page, aclsToGrant);
};

const grantAllAcls = page => {
  grantAllAclsExcept(page, []);
};

module.exports = {grantAllAcls, grantAllAclsExcept};

const allAcls = [
  'oro_config_system',
  'pim_user_user_index',
  'pim_user_user_create',
  'pim_user_user_edit',
  'pim_user_user_remove',
  'pim_user_group_index',
  'pim_user_group_create',
  'pim_user_group_edit',
  'pim_user_group_remove',
  'pim_user_role_index',
  'pim_user_role_create',
  'pim_user_role_edit',
  'pim_user_role_remove',
  'pim_enrich_api_connection_manage',
  'pim_enrich_locale_index',
  'pim_enrich_currency_index',
  'pim_enrich_currency_toggle',
  'pim_enrich_channel_index',
  'pim_enrich_channel_create',
  'pim_enrich_channel_edit',
  'pim_enrich_channel_remove',
  'pim_enrich_channel_history',
  'pim_pdf_generator_product_download',
  'pim_enrich_product_index',
  'pim_enrich_product_create',
  'pim_enrich_product_edit_attributes',
  'pim_enrich_product_remove',
  'pim_enrich_product_add_attribute',
  'pim_enrich_product_remove_attribute',
  'pim_enrich_product_categories_view',
  'pim_enrich_associations_view',
  'pim_enrich_associations_edit',
  'pim_enrich_associations_remove',
  'pim_enrich_product_change_family',
  'pim_enrich_product_change_state',
  'pim_enrich_product_add_to_groups',
  'pim_enrich_product_comment',
  'pim_enrich_product_history',
  'pim_enrich_product_model_create',
  'pim_enrich_product_model_edit_attributes',
  'pim_enrich_product_model_categories_view',
  'pim_enrich_product_model_history',
  'pim_enrich_product_model_remove',
  'pim_enrich_mass_edit',
  'pim_enrich_product_category_list',
  'pim_enrich_product_category_create',
  'pim_enrich_product_category_edit',
  'pim_enrich_product_category_remove',
  'pim_enrich_product_category_history',
  'pim_enrich_group_index',
  'pim_enrich_group_create',
  'pim_enrich_group_edit',
  'pim_enrich_group_remove',
  'pim_enrich_group_history',
  'pim_enrich_associationtype_index',
  'pim_enrich_associationtype_create',
  'pim_enrich_associationtype_edit',
  'pim_enrich_associationtype_remove',
  'pim_enrich_associationtype_history',
  'pim_enrich_family_index',
  'pim_enrich_family_create',
  'pim_enrich_family_edit_properties',
  'pim_enrich_family_edit_attributes',
  'pim_enrich_family_edit_variants',
  'pim_enrich_family_remove',
  'pim_enrich_family_history',
  'pim_enrich_family_variant_remove',
  'pim_enrich_attributegroup_index',
  'pim_enrich_attributegroup_create',
  'pim_enrich_attributegroup_edit',
  'pim_enrich_attributegroup_remove',
  'pim_enrich_attributegroup_sort',
  'pim_enrich_attributegroup_add_attribute',
  'pim_enrich_attributegroup_remove_attribute',
  'pim_enrich_attributegroup_history',
  'pim_enrich_attribute_index',
  'pim_enrich_attribute_create',
  'pim_enrich_attribute_edit',
  'pim_enrich_attribute_remove',
  'pim_enrich_attribute_sort',
  'pim_enrich_attribute_history',
  'pim_enrich_grouptype_index',
  'pim_enrich_grouptype_create',
  'pim_enrich_grouptype_edit',
  'pim_enrich_grouptype_remove',
  'pim_analytics_system_info_index',
  'pim_api_overall_access',
  'pim_api_attribute_list',
  'pim_api_attribute_edit',
  'pim_api_attribute_option_list',
  'pim_api_attribute_option_edit',
  'pim_api_attribute_group_list',
  'pim_api_attribute_group_edit',
  'pim_api_category_list',
  'pim_api_category_edit',
  'pim_api_channel_list',
  'pim_api_channel_edit',
  'pim_api_locale_list',
  'pim_api_family_list',
  'pim_api_family_edit',
  'pim_api_family_variant_list',
  'pim_api_family_variant_edit',
  'pim_api_currency_list',
  'pim_api_association_type_list',
  'pim_api_association_type_edit',
  'pim_importexport_export_profile_index',
  'pim_importexport_export_profile_create',
  'pim_importexport_export_profile_show',
  'pim_importexport_export_profile_edit',
  'pim_importexport_export_profile_remove',
  'pim_importexport_export_profile_launch',
  'pim_importexport_export_profile_property_show',
  'pim_importexport_export_profile_property_edit',
  'pim_importexport_export_profile_history',
  'pim_importexport_export_profile_content_show',
  'pim_importexport_export_profile_content_edit',
  'pim_importexport_import_profile_index',
  'pim_importexport_import_profile_create',
  'pim_importexport_import_profile_show',
  'pim_importexport_import_profile_edit',
  'pim_importexport_import_profile_remove',
  'pim_importexport_import_profile_launch',
  'pim_importexport_import_profile_history',
  'pim_importexport_export_execution_index',
  'pim_importexport_export_execution_show',
  'pim_importexport_export_execution_download_log',
  'pim_importexport_export_execution_download_files',
  'pim_importexport_import_execution_index',
  'pim_importexport_import_execution_show',
  'pim_importexport_import_execution_download_log',
  'pim_importexport_import_execution_download_files',
  'pim_enrich_job_tracker_index',
  'pimee_catalog_rule_rule_view_permissions',
  'pimee_catalog_rule_rule_delete_permissions',
  'pimee_catalog_rule_rule_impacted_product_count_permissions',
  'pimee_catalog_rule_rule_execute_permissions',
  'akeneo_referenceentity_reference_entity_create',
  'akeneo_referenceentity_reference_entity_edit',
  'akeneo_referenceentity_reference_entity_delete',
  'akeneo_referenceentity_reference_entity_manage_permission',
  'akeneo_referenceentity_record_create',
  'akeneo_referenceentity_record_edit',
  'akeneo_referenceentity_record_list_product',
  'akeneo_referenceentity_record_delete',
  'akeneo_referenceentity_attribute_create',
  'akeneo_referenceentity_attribute_delete',
  'akeneo_referenceentity_attribute_edit',
  'akeneo_referenceentity_option_edit',
  'akeneo_referenceentity_option_delete',
  'akeneo_assetmanager_asset_family_create',
  'akeneo_assetmanager_asset_family_edit',
  'akeneo_assetmanager_asset_family_delete',
  'akeneo_assetmanager_asset_family_manage_permission',
  'akeneo_assetmanager_asset_create',
  'akeneo_assetmanager_asset_edit',
  'akeneo_assetmanager_asset_list_product',
  'akeneo_assetmanager_asset_delete',
  'akeneo_assetmanager_attribute_create',
  'akeneo_assetmanager_attribute_delete',
  'akeneo_assetmanager_attribute_edit',
  'akeneo_assetmanager_option_edit',
  'akeneo_assetmanager_option_delete',
  'pimee_enrich_locale_edit_permissions',
  'pimee_enrich_category_edit_permissions',
  'pimee_enrich_attribute_group_edit_permissions',
  'pimee_importexport_export_profile_edit_permissions',
  'pimee_importexport_import_profile_edit_permissions',
  'pim_api_asset_category_list',
  'pim_api_asset_category_edit',
  'pim_api_asset_list',
  'pim_api_asset_edit',
  'pimee_revert_product_version_revert',
  'pimee_workflow_published_product_index',
  'pimee_sso_configuration',
];
