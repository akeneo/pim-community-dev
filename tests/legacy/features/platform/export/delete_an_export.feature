@javascript
Feature: Delete export
  In order to delete an import job that have been created
  As an administrator
  I need to be able to view a list of them
  And I need to delete one of them or cancel my operation

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Peter"
    And I am on the exports grid
    Then the grid should contain 22 elements

  Scenario: Successfully delete an export job from the export grid
    Given I delete the "CSV footwear product export" job
    When I confirm the deletion
    Then I should see the flash message "Export profile successfully removed"
    And the grid should contain 21 elements
    And I should not see export profile "CSV footwear product export"
