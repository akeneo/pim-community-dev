@javascript
Feature: Filter products
  In order to filter products in the catalog
  As a regular user
  I need to be able to filter products with multiples prices filters

  Background:
    Given the "default" catalog configuration
    And the following family:
      | code      |
      | furniture |
      | library   |
    And the following attributes:
      | code      | label     | type   | useable as grid filter |
      | margin    | Margin    | prices | yes                    |
      | transport | Transport | prices | yes                    |
    And the following "margin" attribute options: Black and Green
    And the following "transport" attribute options: Black and White and Red
    And the following products:
      | sku    | family    | transport | margin |
      | BOOK   | library   |           |        |
      | MUG-1  | furniture | 2 EUR     | 7 EUR  |
      | MUG-2  | furniture | 15 EUR    | 7 EUR  |
      | MUG-3  | furniture | 15 EUR    | 7 EUR  |
      | MUG-4  | furniture | 15 EUR    | 7 EUR  |
      | MUG-5  | furniture |           | 7 EUR  |
      | POST-1 | furniture | 15 EUR    |        |
      | POST-2 | furniture | 15 EUR    |        |
      | POST-3 | furniture | 30 EUR    |        |
    And the following product groups:
      | code   | label  | axis              | type    | products                          |
      | MUG    | Mug    | margin, transport | VARIANT | MUG-1, MUG-2, MUG-3, MUG-4, MUG-5 |
      | POSTIT | Postit | transport         | X_SELL  | POST-1, POST-2, POST-3            |
      | EMPTY  | Empty  |                   | X_SELL  |                                   |
    And I am logged in as "Mary"

  Scenario: Successfully filter products with the sames attributes
    Given I am on the products page
    And I show the filter "Transport"
    And I filter by "Transport" with value "= 15 EUR"
    And I show the filter "Margin"
    And I filter by "Margin" with value "= 7 EUR"
    Then the grid should contain 3 elements
    And I should see entities "MUG-2" and "MUG-3" and "MUG-4"
    And I hide the filter "Transport"
    And I hide the filter "Margin"

  Scenario: Successfully filter product without commons attributes
    Given I am on the products page
    And I show the filter "Transport"
    And I filter by "Transport" with value "= 30 EUR"
    And I show the filter "Margin"
    And I filter by "Margin" with value "= 7 EUR"
    Then the grid should contain 0 elements
    And I hide the filter "Transport"
    And I hide the filter "Margin"

  Scenario: Successfully filter only one product
    Given I am on the products page
    And I show the filter "Transport"
    And I filter by "Transport" with value "= 2 EUR"
    And I show the filter "Margin"
    And I filter by "Margin" with value "= 7 EUR"
    Then the grid should contain 1 elements
    And I should see entities "MUG-1"
    And I hide the filter "Transport"
    And I hide the filter "Margin"
