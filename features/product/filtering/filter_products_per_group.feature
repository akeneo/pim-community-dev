@javascript
Feature: Filter products
  In order to filter products in the catalog
  As a regular user
  I need to be able to filter products in the catalog

  Background:
    Given the "default" catalog configuration
    And the following family:
      | code      |
      | furniture |
      | library   |
    And the following attribute:
      | code  | label | type         |
      | color | Color | simpleselect |
    And the following "color" attribute options: Black and White
    And the following products:
      | sku    | family    |
      | BOOK   | library   |
      | MUG-1  | furniture |
      | MUG-2  | furniture |
      | POSTIT | furniture |
    And the following product groups:
      | code   | label  | attributes | type    | products     |
      | MUG    | Mug    | color      | VARIANT | MUG-1, MUG-2 |
      | POSTIT | Postit |            | X_SELL  | POSTIT       |
      | EMPTY  | Empty  |            | X_SELL  |              |
    And I am logged in as "Mary"

  Scenario: Successfully display datagrid with group
    Given I am on the products page
    Then the grid should contain 4 elements
    And I should see products BOOK, MUG-1, MUG-2 and POSTIT
    And I should see the filters Groups

  Scenario: Successfully filter by group
    Given I am on the products page
    When I filter by "Groups" with value "Mug"
    Then the grid should contain 2 elements
    And I should see products MUG-1 and MUG-2
    And I should not see products BOOK and POSTIT

  Scenario: Successfully filter by a group without products linked
    Given I am on the products page
    When I filter by "Groups" with value "Empty"
    Then the grid should contain 0 element
    And I should not see products BOOK, MUG-1, MUG-2 and POSTIT
