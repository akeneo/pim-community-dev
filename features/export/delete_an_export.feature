@javascript
Feature: Delete export
  In order to delete an import job that have been created
  As an administrator
  I need to be able to view a list of them
  And I need to delete one of them or cancel my operation

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Peter"
    And I am on the exports page
    When I change the page size to 100
    Then the grid should contain 13 elements
    When I delete the "csv_footwear_product_export" job

  Scenario: Successfully delete an export job
    Given I confirm the deletion
    Then I should see flash message "Export profile successfully removed"
    And the grid should contain 12 elements
    And I should not see export profile "csv_footwear_product_export"

  Scenario: Successfully cancel the deletion of an export job
    Given I cancel the deletion
    Then the grid should contain 13 elements
    And I should see export profile "csv_footwear_product_export"
