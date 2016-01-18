@javascript
Feature: Filter products with multiples prices filters
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
      | code      | label     | type   | useable_as_grid_filter |
      | margin    | Margin    | prices | yes                    |
      | transport | Transport | prices | yes                    |
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
    And I am logged in as "Mary"
    And I am on the products page
    And I show the filter "Transport"
    And I show the filter "Margin"

  Scenario: Successfully filter products with the sames attributes
    Given I filter by "Transport" with value "= 15 EUR"
    And I should be able to use the following filters:
      | filter | value       | result                 |
      | Margin | > 7 EUR     |                        |
      | Margin | >= 7.01 EUR |                        |
      | Margin | < 7 EUR     |                        |
      | Margin | <= 6.99 EUR |                        |
      | Margin | > 6 EUR     | MUG-2, MUG-3 and MUG-4 |
      | Margin | > 6.99 EUR  | MUG-2, MUG-3 and MUG-4 |
      | Margin | < 8 EUR     | MUG-2, MUG-3 and MUG-4 |
      | Margin | < 7.01 EUR  | MUG-2, MUG-3 and MUG-4 |
      | Margin | >= 7 EUR    | MUG-2, MUG-3 and MUG-4 |
      | Margin | <= 7 EUR    | MUG-2, MUG-3 and MUG-4 |
      | Margin | = 7 EUR     | MUG-2, MUG-3 and MUG-4 |
      | Margin | = 0 EUR     |                        |
      | Margin | > 0 EUR     | MUG-2, MUG-3 and MUG-4 |
    And I hide the filter "Transport"
    And I hide the filter "Margin"

  Scenario: Successfully filter product without commons attributes
    Given I filter by "Margin" with value "= 7 EUR"
    And I should be able to use the following filters:
      | filter    | value        | result                        |
      | Transport | > 15 EUR     |                               |
      | Transport | >= 15.01 EUR |                               |
      | Transport | < 15 EUR     | MUG-1                         |
      | Transport | <= 14.99 EUR | MUG-1                         |
      | Transport | > 14 EUR     | MUG-2, MUG-3 and MUG-4        |
      | Transport | > 14.99 EUR  | MUG-2, MUG-3 and MUG-4        |
      | Transport | < 16 EUR     | MUG-1, MUG-2, MUG-3 and MUG-4 |
      | Transport | < 15.01 EUR  | MUG-1, MUG-2, MUG-3 and MUG-4 |
      | Transport | >= 15 EUR    | MUG-2, MUG-3 and MUG-4        |
      | Transport | <= 15 EUR    | MUG-1, MUG-2, MUG-3 and MUG-4 |
      | Transport | = 15 EUR     | MUG-2, MUG-3 and MUG-4        |
      | Transport | = 0 EUR      |                               |
      | Transport | > 0 EUR      | MUG-1, MUG-2, MUG-3 and MUG-4 |
    And I hide the filter "Transport"
    And I hide the filter "Margin"
