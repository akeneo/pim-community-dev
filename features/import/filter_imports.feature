@javascript
Feature: Filter import profiles
  In order to filter import profiles in the catalog
  As a user
  I need to be able to filter import profiles in the catalog

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "admin"
    And I am on the imports page

  Scenario: Successfully display filters
    Then I should see the filters Code, Label, Job, Connector and Status
    And the grid should contain 6 elements

  Scenario: Successfully filter by code
    Given I filter by "Code" with value "at"
    Then the grid should contain 3 elements
    And I should see import profiles footwear_association_import, footwear_attribute_import and footwear_category_import

  Scenario: Successfully filter by label
    Given I filter by "Label" with value "Product"
    Then the grid should contain 1 element
    And I should see import profile footwear_product_import

  Scenario: Successfully filter by job
    Given I filter by "Job" with value "group_import"
    Then the grid should contain 1 element
    And I should see import profile footwear_group_import

  Scenario: Successfully filter by connector
    Given I filter by "Connector" with value "Akeneo CSV Connector"
    Then the grid should contain 6 elements
    And I should see import profiles footwear_product_import, footwear_category_import, footwear_association_import, footwear_group_import, footwear_attribute_import and footwear_option_import

  Scenario: Successfully filter by status
    Given I filter by "Status" with value "Ready"
    Then the grid should contain 6 elements
    And I should see import profiles footwear_product_import, footwear_category_import, footwear_association_import, footwear_group_import, footwear_attribute_import and footwear_option_import
