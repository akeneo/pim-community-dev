@javascript
Feature: Filter products
  In order to filter products in the catalog
  As a regular user
  I need to be able to filter products in the catalog

  Background:
    Given the "default" catalog configuration
    And the following group type:
      | code    | label-en_US |
      | related | Related     |
    And the following product groups:
      | code   | label-en_US | type    |
      | POSTIT | Postit      | X_SELL  |
      | EMPTY  | Empty       | X_SELL  |
      | MUG    | Mug         | related |
    And the following products:
      | sku    | groups   |
      | BOOK   |          |
      | MUG-1  | MUG      |
      | MUG-2  | MUG      |
      | POSTIT | POSTIT   |
    And I am logged in as "Mary"

  Scenario: Successfully display datagrid with group
    Given I am on the products grid
    Then the grid should contain 4 elements
    And I should see products BOOK, MUG-1, MUG-2 and POSTIT
    And I should see the filters groups

  Scenario: Successfully filter by group
    Given I am on the products grid
    When I filter by "groups" with operator "" and value "Mug"
    Then the grid should contain 2 elements
    And I should see products MUG-1 and MUG-2
    And I should not see products BOOK and POSTIT

  Scenario: Successfully filter by a group without products linked
    Given I am on the products grid
    When I filter by "groups" with operator "" and value "Empty"
    Then the grid should contain 0 element
    And I should not see products BOOK, MUG-1, MUG-2 and POSTIT

  Scenario: Successfully keep the group filter on page reload
    Given I am on the products grid
    When I filter by "groups" with operator "in list" and value "Mug"
    And I reload the page
    Then the criteria of "groups" filter should be ""Mug""
    And the grid should contain 2 elements
