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
    Then the grid should contain 8 elements
    And I should see export profiles clothing_attribute_export, clothing_category_export, clothing_group_export, clothing_product_export and clothing_rule_export
    And I am on the imports page
    And the grid should contain 8 elements
    And I should see import profiles clothing_attribute_import, clothing_group_import, clothing_product_import, clothing_rule_import and clothing_product_proposal_import

  Scenario: Successfully view job profiles depending on given permissions for administrator
    Given I am logged in as "Peter"
    And I am on the exports page
    Then the grid should contain 10 elements
    And I am on the imports page
    Then the grid should contain 11 elements
