@javascript
Feature: Filter products with multiples prices filters
  In order to filter products in the catalog
  As a regular user
  I need to be able to filter products with multiples prices filters

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | code      | label-en_US | type                         | useable_as_grid_filter | group | decimals_allowed |
      | margin    | Margin      | pim_catalog_price_collection | 1                      | other | 0                |
      | transport | Transport   | pim_catalog_price_collection | 1                      | other | 0                |
    And the following family:
      | code      | attributes       |
      | furniture | margin,transport |
      | library   | margin,transport |
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
    And I am on the products grid
    And I show the filter "transport"
    And I show the filter "margin"

  Scenario: Successfully filter products with the sames attributes
    Given I filter by "transport" with operator "=" and value "15 EUR"
    And I should be able to use the following filters:
      | filter | operator | value    | result                 |
      | margin | >        | 7 EUR    |                        |
      | margin | >=       | 7.01 EUR |                        |
      | margin | <        | 7 EUR    |                        |
      | margin | <=       | 6.99 EUR |                        |
      | margin | >        | 6 EUR    | MUG-2, MUG-3 and MUG-4 |
      | margin | >        | 6.99 EUR | MUG-2, MUG-3 and MUG-4 |
      | margin | <        | 8 EUR    | MUG-2, MUG-3 and MUG-4 |
      | margin | <        | 7.01 EUR | MUG-2, MUG-3 and MUG-4 |
      | margin | >=       | 7 EUR    | MUG-2, MUG-3 and MUG-4 |
      | margin | <=       | 7 EUR    | MUG-2, MUG-3 and MUG-4 |
      | margin | =        | 7 EUR    | MUG-2, MUG-3 and MUG-4 |
      | margin | =        | 0 EUR    |                        |
      | margin | >        | 0 EUR    | MUG-2, MUG-3 and MUG-4 |
    And I hide the filter "transport"
    And I hide the filter "margin"

  Scenario: Successfully filter product without commons attributes
    Given I filter by "margin" with operator "=" and value "7 EUR"
    And I should be able to use the following filters:
      | filter    | operator | value     | result                        |
      | transport | >        | 15 EUR    |                               |
      | transport | >=       | 15.01 EUR |                               |
      | transport | <        | 15 EUR    | MUG-1                         |
      | transport | <=       | 14.99 EUR | MUG-1                         |
      | transport | >        | 14 EUR    | MUG-2, MUG-3 and MUG-4        |
      | transport | >        | 14.99 EUR | MUG-2, MUG-3 and MUG-4        |
      | transport | <        | 16 EUR    | MUG-1, MUG-2, MUG-3 and MUG-4 |
      | transport | <        | 15.01 EUR | MUG-1, MUG-2, MUG-3 and MUG-4 |
      | transport | >=       | 15 EUR    | MUG-2, MUG-3 and MUG-4        |
      | transport | <=       | 15 EUR    | MUG-1, MUG-2, MUG-3 and MUG-4 |
      | transport | =        | 15 EUR    | MUG-2, MUG-3 and MUG-4        |
      | transport | =        | 0 EUR     |                               |
      | transport | >        | 0 EUR     | MUG-1, MUG-2, MUG-3 and MUG-4 |
    And I hide the filter "transport"
    And I hide the filter "margin"
