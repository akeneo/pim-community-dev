@javascript
Feature: Browse imports
  In order to view the list of import job instances that have been created
  As a user
  I need to be able to view a list of them

  Scenario: Successfully display the import jobs
    Given a "footwear" catalog configuration
    And I am logged in as "admin"
    And I am on the imports page
    Then the grid should contain 6 elements
    And I should see the columns Code, Label, Job, Connector and Status
    And I should see import profiles footwear_product_import, footwear_category_import, footwear_association_import, footwear_group_import, footwear_attribute_import and footwear_option_import
    And I should not see export profile footwear_product_export
    And the row "footwear_product_import" should contain:
      | column    | value                |
      | connector | Akeneo CSV Connector |
