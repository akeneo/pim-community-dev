@javascript
Feature: Sort group types
  In order to easily manage group types in the catalog
  As an administrator
  I need to be able to sort group types by several columns

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully sort the group types in the grid
    Given I am on the group types page
    Then the rows should be sorted ascending by code
    And I should be able to sort the rows by code and label
