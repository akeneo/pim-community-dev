@javascript
Feature: Filter products per price
  In order to filter products in the catalog per price
  As a user
  I need to be able to filter products per price in the catalog

  Background:
    Given the "default" catalog configuration
    And the following family:
      | code      |
      | furniture |
      | library   |
    And the following products:
      | sku    | family    | enabled |
      | postit | furniture | yes     |
      | book   | library   | no      |
    And a "postit" product
    And a "book" product
    And the following attributes:
      | label | required | translatable | scopable | type   | useable as grid filter |
      | price | no       | no           | yes      | prices | yes                    |
    And the following product values:
      | product | attribute | scope     | value  |
      | postit  | SKU       |           | postit |
      | postit  | price     | mobile    | 10.5   |
      | postit  | price     | ecommerce | 12.5   |
      | book    | SKU       |           | book   |
      | book    | price     | mobile    | 20     |
      | book    | price     | ecommerce | 22.5   |
    And I am logged in as "admin"

  Scenario: Successfully filter products by price
    Given I am on the products page
    Then I should see the filter SKU
    And I should not see the filter Price
    And the grid should contain 2 elements
    And I should see products postit and book
    And I should be able to use the following filters:
      | filter | value      | result          |
      | Price  | >= 20 EUR  | book            |
      | Price  | > 12.5 EUR | book            |
      | Price  | = 12.5 EUR | postit          |
      | Price  | < 20 EUR   | postit          |
      | Price  | <= 13 EUR  | postit          |
      | Price  | <= 20 EUR  | postit and book |
      | Price  | > 40.5 EUR |                 |
