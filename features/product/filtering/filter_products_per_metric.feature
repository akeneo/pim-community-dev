@javascript
Feature: Filter products per metric
  In order to filter products in the catalog per metric
  As a regular user
  I need to be able to filter products per metric

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | label-en_US | scopable | type               | useable_as_grid_filter | metric_family | default_metric_unit | decimals_allowed | negative_allowed | group | code   |
      | Weight      | 1        | pim_catalog_metric | 1                      | Weight        | GRAM                | 1                | 0                | other | weight |
    And the following family:
      | code      | attributes |
      | furniture | weight     |
      | library   | weight     |
    And the following products:
      | sku    | family    | enabled | weight-ecommerce | weight-mobile |
      | postit | furniture | yes     | 120 GRAM         |               |
      | book   | library   | no      | 0.2 KILOGRAM     |               |
      | mug    |           | yes     |                  | 120 GRAM      |
      | pen    |           | yes     |                  |               |
    And I am logged in as "Mary"

  Scenario: Successfully filter products by metric
    Given I am on the products grid
    Then I should not see the filter weight
    And the grid should contain 4 elements
    And I should see products postit and book
    And I should be able to use the following filters:
      | filter | operator     | value         | result          |
      | weight | >=           | 200 Gram      | book            |
      | weight | >            | 120 Gram      | book            |
      | weight | =            | 120 Gram      | postit          |
      | weight | <            | 200 Gram      | postit          |
      | weight | <=           | 120 Gram      | postit          |
      | weight | <=           | 0.25 Kilogram | postit and book |
      | weight | >            | 4 Kilogram    |                 |
      | weight | is empty     |               | mug and pen     |
      | weight | is not empty |               | postit and book |
