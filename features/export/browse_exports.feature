@javascript
Feature: Browse export profiles
  In order to view the list of export jobs that have been created
  As a user
  I need to be able to view a list of them

  Scenario: Successfully display the export jobs
    Given a "footwear" catalog configuration
    And I am logged in as "admin"
    And I am on the exports page
    Then the grid should contain 6 elements
    And I should see the columns Code, Label, Job, Connector and Status
    And I should see export profiles footwear_product_export, footwear_category_export, footwear_association_export, footwear_group_export, footwear_attribute_export and footwear_option_export
    And I should not see import profile footwear_product_import
    And the row "footwear_product_export" should contain:
      | column    | value                |
      | connector | Akeneo CSV Connector |
