@javascript
Feature: Filter products per price
  In order to filter products in the catalog per price
  As an user
  I need to be able to filter products per price in the catalog

  Background:
    Given the following currencies:
      | code | activated |
      | USD  | yes       |
      | EUR  | yes       |
      | GBP  | no        |
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
    And the following product attributes:
      | label       | required | translatable | scopable | type   |
      | SKU         | yes      | no           | no       | text   |
      | price       | no       | no           | yes      | prices |
    And the following product values:
      | product | attribute   | locale |scope      | value                    |
      | postit  | SKU         |        |           | postit                   |
      | postit  | price       |        | mobile    | 10.5                     |
      | postit  | price       |        | ecommerce | 12.5                     |
      | book    | SKU         |        |           | book                     |
      | book    | price       |        | mobile    | 20                       |
      | book    | price       |        | ecommerce | 22.5                     |
    And I am logged in as "admin"

  Scenario: Successfully display filters
    Given I am on the products page
    Then I should see the filters SKU, Price
    And the grid should contain 2 elements
    And I should see products postit and book

  Scenario: Successfully filter per Price
    Given I am on the products page
    When I filter per price with value "20" and currency "Euro"
    Then the grid should contain 1 element
    And I should see products book
    And I should not see products postit
