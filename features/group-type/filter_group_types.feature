@javascript
Feature: Filter group types
  In order to easily find group types in the catalog
  As an administrator
  I need to be able to filter group types

  Background:
    Given the "default" catalog configuration
    And the following group types:
      | code    | label-en_US |
      | related | Relation    |
      | special | Special     |
    And I am logged in as "Peter"
    Given I am on the group types page
    Then the grid should contain 4 elements

  Scenario: Successfully filter group types
    When I show the filter "code"
    And I filter by "code" with operator "contains" and value "rel"
    Then the grid should contain 1 element
    Then I should see entity related

  Scenario: Successfully search on label
    When I search "Spec"
    Then the grid should contain 1 element
    Then I should see entity special
