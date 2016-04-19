@javascript
Feature: Browse job executions
  In order to view the list of job executions
  As a product manager
  I need to be able to view a list of executed jobs

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully view job executions depending on given permissions
    Given I am on the exports page
    And I launch the "clothing_product_export" export job
    And I launch the "clothing_category_export" export job
    And I launch the "clothing_attribute_export" export job
    And I launch the "clothing_category_export" export job
    And I launch the "clothing_option_export" export job
    And I launch the "csv_clothing_product_import" import job
    When I am on the export executions page
    Then the grid should contain 5 elements
    And I should see export profiles clothing_product_export, clothing_category_export, clothing_attribute_export, clothing_category_export and clothing_option_export
    When I am on the "clothing_category_export" export job edit page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to execute job profile | Redactor |
      | Allowed to edit job profile    | Redactor |
    And I save the job profile
    Then I am on the export executions page
    And the grid should contain 3 elements
    And I should see export profiles clothing_product_export, clothing_attribute_export and clothing_option_export
