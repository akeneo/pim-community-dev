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
      | code  | label-en_US | type                     | group |
      | color | Color       | pim_catalog_simpleselect | other |
    And the following "color" attribute options: Black and White
    And the following variant groups:
      | code | label-en_US | axis  | type    |
      | MUG  | Mug         | color | VARIANT |
    And the following product groups:
      | code   | label-en_US | type   |
      | POSTIT | Postit      | X_SELL |
      | EMPTY  | Empty       | X_SELL |
    And the following products:
      | sku    | family    | color | groups   |
      | BOOK   | library   |       |          |
      | MUG-1  | furniture | white | MUG      |
      | MUG-2  | furniture | black | MUG      |
      | POSTIT | furniture |       | POSTIT   |
    And I am logged in as "Mary"

  Scenario: Successfully display datagrid with group
    Given I am on the products page
    Then the grid should contain 4 elements
    And I should see products BOOK, MUG-1, MUG-2 and POSTIT
    And I should see the filters groups

  Scenario: Successfully filter by group
    Given I am on the products page
    When I filter by "groups" with operator "" and value "Mug"
    Then the grid should contain 2 elements
    And I should see products MUG-1 and MUG-2
    And I should not see products BOOK and POSTIT

  Scenario: Successfully filter by a group without products linked
    Given I am on the products page
    When I filter by "groups" with operator "" and value "Empty"
    Then the grid should contain 0 element
    And I should not see products BOOK, MUG-1, MUG-2 and POSTIT

  Scenario: Successfully keep the group filter on page reload
    Given I am on the products page
    When I filter by "groups" with operator "in list" and value "Mug"
    And I reload the page
    Then the criteria of "groups" filter should be ""Mug""
    And the grid should contain 2 elements
