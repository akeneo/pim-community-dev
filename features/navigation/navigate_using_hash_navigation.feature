@javascript
Feature: Navigate the application in hash navigation mode
  In order to provide fluid user experience when using the application
  As an administrator
  I need to allow users to navigate from page to page without a complete page reload

  Scenario: Navigate in hash navigation mode
    Given the "default" catalog configuration
    And I am logged in as "Peter"
    And I am on the home page
    Then I should be able visit the following pages without errors
      | page                                    |
      | pim_enrich_association_type_index       |
      | pim_enrich_attributegroup_create        |
      | pim_enrich_attributegroup_index         |
      | pim_enrich_categorytree_create          |
      | pim_enrich_channel_create               |
      | pim_enrich_channel_index                |
      | pim_enrich_currency_index               |
      | pim_enrich_family_index                 |
      | pim_enrich_group_index                  |
      | pim_enrich_group_type_index             |
      | pim_enrich_locale_index                 |
      | pim_enrich_product_index                |
      | pim_enrich_attribute_index              |
      | pim_enrich_variant_group_index          |
      | pim_importexport_export_profile_index   |
      | pim_importexport_export_execution_index |
      | pim_importexport_import_profile_index   |
      | pim_importexport_import_execution_index |
      | oro_pinbar_help                         |
      | oro_user_create                         |
      | oro_user_group_create                   |
      | oro_user_group_index                    |
      | oro_user_index                          |
      | oro_user_profile_update                 |
      | oro_user_profile_view                   |
      | oro_user_role_create                    |
      | oro_user_role_index                     |
      | oro_user_status_create                  |
      | oro_user_status_list                    |
