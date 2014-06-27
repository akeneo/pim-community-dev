@javascript
Feature: Delete a group type
  In order to manage group types in the catalog
  As an administrator
  I need to be able to delete group types

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully delete a group type from the edit page
    Given I edit the "X_SELL" group type
    When I press the "Delete" button
    And I confirm the deletion
    Then the grid should contain 1 element
    And I should not see group type "X_SELL"
