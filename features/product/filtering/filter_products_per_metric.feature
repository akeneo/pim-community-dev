@javascript
Feature: Filter products per metric
  In order to filter products in the catalog per metric
  As a regular user
  I need to be able to filter products per metric

  Background:
    Given the "default" catalog configuration
    And the following family:
      | code      |
      | furniture |
      | library   |
    And the following attributes:
      | label  | scopable | type   | useable as grid filter | metric family | default metric unit | decimals allowed |
      | Weight | yes      | metric | yes                    | Weight        | GRAM                | yes              |
    And the following products:
      | sku    | family    | enabled | weight-ecommerce | weight-mobile |
      | postit | furniture | yes     | 120 GRAM         |               |
      | book   | library   | no      | 0.2 KILOGRAM     |               |
      | mug    |           | yes     |                  | 120 GRAM      |
      | pen    |           | yes     |                  |               |
    And I am logged in as "Mary"

  Scenario: Successfully filter products by metric
    Given I am on the products page
    Then I should see the filter SKU
    And I should not see the filter Weight
    And the grid should contain 4 elements
    And I should see products postit and book
    And I should be able to use the following filters:
      | filter | value            | result          |
      | Weight | >= 200 Gram      | book            |
      | Weight | > 120 Gram       | book            |
      | Weight | = 120 Gram       | postit          |
      | Weight | < 200 Gram       | postit          |
      | Weight | <= 120 Gram      | postit          |
      | Weight | <= 0.25 Kilogram | postit and book |
      | Weight | > 4 Kilogram     |                 |
      | Weight | empty            | mug and pen     |
