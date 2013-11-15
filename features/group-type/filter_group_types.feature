@javascript
Feature: Filter group types
  In order to easily find group types in the catalog
  As a user
  I need to be able to filter group types

  Background:
    Given the "default" catalog configuration
    And there is no group type
    And the following group types:
      | code    | label    |
      | related | Relation |
      | special | Special  |
    And I am logged in as "admin"

  Scenario: Successfully display filters
    Given I am on the group types page
    Then I should see the filters Code and Label
    And the grid should contain 2 elements
    And I should see group types related and special

  Scenario: Successfully filter by code
    Given I am on the group types page
    When I filter by "Code" with value "related"
    Then the grid should contain 1 element
    And I should see group type related
    And I should not see group type special

  Scenario: Successfully filter by label
    Given I am on the group types page
    When I filter by "Label" with value "Special"
    Then the grid should contain 1 elements
    And I should see group type special
    And I should not see group type related

  Scenario: Successfully filter by label and code
    Given I am on the group types page
    When I filter by "Code" with value "ated"
    And I filter by "Label" with value "ation"
    Then the grid should contain 1 element
    And I should see group type related
    And I should not see group type related
