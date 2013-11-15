@javascript
Feature: Delete a group type
  In order to manage group types in the catalog
  As a user
  I need to be able to delete group types

  Background:
    Given the "default" catalog configuration
    And there is no group type
    And the following group types:
      | code    | label   |
      | related | Related |
      | special | Special |
    And I am logged in as "admin"

  Scenario: Successfully delete a group type from the edit page
    Given I edit the "related" group type
    When I press the "Delete" button
    And I confirm the deletion
    Then the grid should contain 1 element
    And I should not see association "related"
