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
    Then the grid should contain 23 elements

  Scenario: Successfully delete an export job from the export grid
    Given I delete the "csv_footwear_product_export" job
    When I confirm the deletion
    Then I should see the flash message "Export profile successfully removed"
    And the grid should contain 22 elements
    And I should not see export profile "csv_footwear_product_export"

  Scenario: Successfully cancel the deletion of an export job
    Given I delete the "csv_footwear_product_export" job
    When I cancel the deletion
    Then the grid should contain 23 elements
    And I should see export profile "csv_footwear_product_export"

  Scenario: Successfully delete an export job from the job edit page
    Given I am on the "csv_footwear_product_export" import job edit page
    When I press the secondary action "Delete"
    And I confirm the deletion
    Then I should see the flash message "Job instance successfully removed"
    And the grid should contain 22 elements
    And I should not see export profile "csv_footwear_product_export"
