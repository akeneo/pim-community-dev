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
      | label | scopable | type   | useable as grid filter | decimals_allowed |
      | Price | yes      | prices | yes                    | yes              |
    And the following products:
      | sku    | family    | enabled | price-mobile | price-ecommerce |
      | postit | furniture | yes     | 10.5 EUR     | 12.5 EUR        |
      | book   | library   | no      | 20 EUR       | 22.5 EUR        |
      | mug    |           | yes     | 10.5 EUR     |                 |
      | pen    |           | yes     |              |                 |
    And I am logged in as "Mary"

  Scenario: Successfully filter products by price
    Given I am on the products page
    Then I should see the filter SKU
    And I should not see the filter Price
    And the grid should contain 4 elements
    And I should see products postit, book and mug
    And I should be able to use the following filters:
      | filter | value      | result          |
      | Price  | >= 20 EUR  | book            |
      | Price  | > 12.5 EUR | book            |
      | Price  | = 12.5 EUR | postit          |
      | Price  | < 20 EUR   | postit          |
      | Price  | <= 13 EUR  | postit          |
      | Price  | <= 23 EUR  | postit and book |
      | Price  | > 40.5 EUR |                 |
    When I show the filter "Price"
    And I filter by price "Price" with empty value on "EUR" currency
    And I should see product mug and pen
