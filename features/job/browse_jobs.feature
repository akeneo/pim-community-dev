@javascript
Feature: Browse jobs
  In order to view the list of jobs
  As a product manager
  I need to be able to view a list of job profiles

  Background:
    Given a "clothing" catalog configuration

  Scenario: Successfully view job profiles depending on given permissions for manager
    Given I am logged in as "Julia"
    And I am on the exports page
    Then the grid should contain 13 elements
    And I should see export profiles clothing_attribute_export, clothing_category_export, clothing_group_export, clothing_product_export and clothing_rule_export
    And I am on the imports page
    And the grid should contain 11 elements
    And I should see import profiles clothing_attribute_import, clothing_group_import, csv_clothing_product_import, clothing_rule_import, clothing_product_proposal_import, xlsx_clothing_product_proposal_import, clothing_product_import_with_rules and xlsx_clothing_product_import_with_rules

  Scenario: Successfully view job profiles depending on given permissions for administrator
    Given I am logged in as "Peter"
    And I am on the exports page
    Then the grid should contain 15 elements
    And I am on the imports page
    Then the grid should contain 14 elements
