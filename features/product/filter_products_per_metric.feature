@javascript
Feature: Filter products per metric
  In order to filter products in the catalog per metric
  As a user
  I need to be able to filter products per metric

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
      | label  | required | translatable | scopable | type   | useable as grid filter | metric family | default metric unit |
      | weight | no       | no           | yes      | metric | yes                    | Weight        | GRAM                |
    And the following product values:
      | product | attribute | value        |
      | postit  | SKU       | postit       |
      | postit  | weight    | 120 GRAM     |
      | book    | SKU       | book         |
      | book    | weight    | 0.2 KILOGRAM |
    And I am logged in as "admin"

  Scenario: Successfully filter products by metric
    Given I am on the products page
    Then I should see the filter SKU
    And I should not see the filter Weight
    And the grid should contain 2 elements
    And I should see products postit and book
    And I should be able to use the following filters:
      | filter | value      | result          |
      | Weight | >= 200 Gram      | book            |
      | Weight | > 120 Gram       | book            |
      | Weight | = 120 Gram       | postit          |
      | Weight | < 200 Gram       | postit          |
      | Weight | <= 120 Gram      | postit          |
      | Weight | <= 0.25 Kilogram | postit and book |
      | Weight | > 4 Kilogram     |                 |
