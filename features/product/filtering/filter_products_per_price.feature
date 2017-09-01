@javascript
Feature: Filter products per price
  In order to filter products in the catalog per price
  As a regular user
  I need to be able to filter products per price

  Background:
    Given the "default" catalog configuration
    And the following family:
      | code      |
      | furniture |
      | library   |
    And the following attributes:
      | label-en_US | scopable | type                         | useable_as_grid_filter | decimals_allowed | group | code  |
      | Price       | 1        | pim_catalog_price_collection | 1                      | 1                | other | price |
    And the following products:
      | sku    | family    | enabled | price-mobile | price-ecommerce |
      | postit | furniture | yes     | 10.5 EUR     | 12.5 EUR        |
      | book   | library   | no      | 20 EUR       | 22.5 EUR        |
      | mug    |           | yes     | 10.5 EUR     |                 |
      | pen    |           | yes     |              |                 |
    And I am logged in as "Mary"

  Scenario: Successfully filter products by price
    Given I am on the products grid
    Then I should see the filter sku
    And I should not see the filter price
    And the grid should contain 4 elements
    And I should see products postit, book and mug
    And I should be able to use the following filters:
      | filter | operator | value    | result          |
      | price  | >=       | 20 EUR   | book            |
      | price  | >        | 22.5 EUR |                 |
      | price  | >=       | 22.5 EUR | book            |
      | price  | >        | 12.5 EUR | book            |
      | price  | >=       | 12.5 EUR | book, postit    |
      | price  | =        | 12.5 EUR | postit          |
      | price  | <        | 20 EUR   | postit          |
      | price  | <        | 10.5 EUR |                 |
      | price  | <=       | 13 EUR   | postit          |
      | price  | <=       | 23 EUR   | postit and book |
      | price  | >        | 40.5 EUR |                 |
    When I show the filter "price"
    And I filter by "price" with operator "is empty" and value " EUR"
    And I should see product mug and pen
    And I filter by "price" with operator "is not empty" and value " EUR"
    And I should see product postit and book
