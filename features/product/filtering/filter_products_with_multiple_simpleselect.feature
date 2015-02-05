@javascript
Feature: Filter products
  In order to filter products in the catalog
  As a regular user
  I need to be able to filter products with multiples simpleselect filters

  Background:
    Given the "default" catalog configuration
    And the following family:
      | code      |
      | furniture |
      | library   |
    And the following attributes:
      | code    | label   | type         | useable as grid filter |
      | color   | Color   | simpleselect | yes                    |
      | company | Company | simpleselect | yes                    |
    And the following "color" attribute options: Black and Green
    And the following "company" attribute options: Black and White and Red
    And the following products:
      | sku    | family    | company | color |
      | BOOK   | library   |         |       |
      | MUG-1  | furniture | white   | green |
      | MUG-2  | furniture | red     | green |
      | MUG-3  | furniture | red     | green |
      | MUG-4  | furniture | red     | green |
      | MUG-5  | furniture |         | green |
      | POST-1 | furniture | red     |       |
      | POST-2 | furniture | red     |       |
      | POST-3 | furniture | black   |       |
    And the following product groups:
      | code   | label  | axis           | type    | products                          |
      | MUG    | Mug    | color, company | VARIANT | MUG-1, MUG-2, MUG-3, MUG-4, MUG-5 |
      | POSTIT | Postit | company        | X_SELL  | POST-1, POST-2, POST-3            |
      | EMPTY  | Empty  |                | X_SELL  |                                   |
    And I am logged in as "Mary"

  Scenario: Successfully filter products with the sames attributes
    Given I am on the products page
    And I show the filter "Company"
    And I filter by "Company" with value "Red"
    And I show the filter "Color"
    And I filter by "Color" with value "Green"
    Then the grid should contain 3 elements
    And I should see entities "MUG-2" and "MUG-3" and "MUG-4"
    And I hide the filter "Company"
    And I hide the filter "Color"

  Scenario: Successfully filter product without commons attributes
    Given I am on the products page
    And I show the filter "Company"
    And I filter by "Company" with value "Black"
    And I show the filter "Color"
    And I filter by "Color" with value "Green"
    Then the grid should contain 0 elements
    And I hide the filter "Company"
    And I hide the filter "Color"

  Scenario: Successfully filter only one product
    Given I am on the products page
    And I show the filter "Company"
    And I filter by "Company" with value "White"
    And I show the filter "Color"
    And I filter by "Color" with value "Green"
    Then the grid should contain 1 elements
    And I should see entities "MUG-1"
    And I hide the filter "Company"
    And I hide the filter "Color"
