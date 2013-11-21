@javascript
Feature: Filter export profiles
  In order to filter export profiles in the catalog
  As a user
  I need to be able to filter export profiles in the catalog

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "admin"
    And I am on the exports page

  Scenario: Successfully display filters
    Then I should see the filters Code, Label, Job, Connector and Status
    And the grid should contain 6 elements

  Scenario: Successfully filter by code
    Given I filter by "Code" with value "at"
    Then the grid should contain 3 elements
    And I should see export profiles footwear_association_export, footwear_attribute_export and footwear_category_export

  Scenario: Successfully filter by label
    Given I filter by "Label" with value "Product"
    Then the grid should contain 1 element
    And I should see export profile footwear_product_export

  Scenario: Successfully filter by job
    Given I filter by "Job" with value "group_export"
    Then the grid should contain 1 element
    And I should see export profile footwear_group_export

  Scenario: Successfully filter by connector
    Given I filter by "Connector" with value "Akeneo CSV Connector"
    Then the grid should contain 6 elements
    And I should see export profiles footwear_product_export, footwear_category_export, footwear_association_export, footwear_group_export, footwear_attribute_export and footwear_option_export

  Scenario: Successfully filter by status
    Given I filter by "Status" with value "Ready"
    Then the grid should contain 6 elements
    And I should see export profiles footwear_product_export, footwear_category_export, footwear_association_export, footwear_group_export, footwear_attribute_export and footwear_option_export
