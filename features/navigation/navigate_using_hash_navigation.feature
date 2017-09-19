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
      | pim_enrich_associationtype_index        |
      | pim_enrich_attributegroup_create        |
      | pim_enrich_attributegroup_index         |
      | pim_enrich_categorytree_index           |
      | pim_enrich_categorytree_create          |
      | pim_enrich_channel_create               |
      | pim_enrich_channel_index                |
      | pim_enrich_currency_index               |
      | pim_enrich_family_index                 |
      | pim_enrich_group_index                  |
      | pim_enrich_grouptype_index              |
      | pim_enrich_locale_index                 |
      | pim_enrich_product_index                |
      | pim_enrich_attribute_index              |
      | pim_importexport_export_profile_index   |
      | pim_importexport_export_execution_index |
      | pim_importexport_import_profile_index   |
      | pim_importexport_import_execution_index |
      | oro_user_create                         |
      | oro_user_group_create                   |
      | oro_user_group_index                    |
      | oro_user_index                          |
      | oro_user_profile_update                 |
      | oro_user_profile_view                   |
      | oro_user_role_create                    |
      | oro_user_role_index                     |

  Scenario: Reload a page in hash navigation mode
    Given a "footwear" catalog configuration
    And the following family:
      | code       | attributes                                                |
      | high_heels | sku,name,description,price,rating,size,color,manufacturer |
    And the following products:
      | sku      | family   | color | groups |
      | boots    | boots    |       |        |
      | sneakers | sneakers |       |        |
    And I am logged in as "Julia"
    And I am on the products grid
    When I select rows boots and sneakers
    And I press "Bulk actions" on the "Bulk Actions" dropdown button
    And I choose the "Edit common attributes" operation
    And I display the Name attribute
    And I reload the page
    Then I should see the text "Edit common attributes"
