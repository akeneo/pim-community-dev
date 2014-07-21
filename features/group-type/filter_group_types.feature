@javascript
Feature: Filter group types
  In order to easily find group types in the catalog
  As an administrator
  I need to be able to filter group types

  Scenario: Successfully filter group types
    Given the "default" catalog configuration
    And there is no group type
    And the following group types:
      | code    | label    |
      | related | Relation |
      | special | Special  |
    And I am logged in as "Peter"
    Given I am on the group types page
    Then the grid should contain 2 elements
    And I should see group types related and special
    And I should be able to use the following filters:
      | filter | value | result  |
      | Code   | rel   | related |
      | Label  | Spec  | special |
