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
    Then the grid should contain 4 elements
    And I should see export profiles clothing_attribute_export, clothing_category_export, clothing_group_export and clothing_product_export
    And I am on the imports page
    And the grid should contain 3 elements
    And I should see import profiles clothing_attribute_import, clothing_group_import and clothing_product_import

  Scenario: Successfully view job profiles depending on given permissions for administrator
    Given I am logged in as "Peter"
    And I am on the exports page
    Then the grid should contain 6 elements
    And I am on the imports page
    Then the grid should contain 6 elements
