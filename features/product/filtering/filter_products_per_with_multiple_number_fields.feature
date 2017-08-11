@javascript
Feature: Filter products with multiples number fields filters
  In order to filter products in the catalog
  As a regular user
  I need to be able to filter products with multiples number fields filters

  Background:
    Given the "default" catalog configuration
    And the following family:
      | code      |
      | furniture |
      | library   |
    And the following attributes:
      | code      | label-en_US | type               | useable_as_grid_filter | group | decimals_allowed | negative_allowed |
      | component | Component   | pim_catalog_number | 1                      | other | 0                | 0                |
      | supplier  | Supplier    | pim_catalog_number | 1                      | other | 0                | 0                |
    And the following products:
      | sku    | family    | supplier | component |
      | BOOK   | library   |          |           |
      | MUG-1  | furniture | 12       | 16        |
      | MUG-2  | furniture | 03       | 16        |
      | MUG-3  | furniture | 03       | 16        |
      | MUG-4  | furniture | 03       | 16        |
      | MUG-5  | furniture |          | 16        |
      | POST-1 | furniture | 03       |           |
      | POST-2 | furniture | 03       |           |
      | POST-3 | furniture | 01       |           |
    And I am logged in as "Mary"
    And I am on the products grid
    And I show the filter "supplier"
    And I show the filter "component"

  Scenario: Successfully filter products with the sames attributes
    Given I filter by "supplier" with operator "=" and value "03"
    And I should be able to use the following filters:
      | filter    | operator     | value | result                 |
      | component | is empty     |       | POST-1, POST-2         |
      | component | is not empty |       | MUG-2, MUG-3 and MUG-4 |
      | component | >            | 16    |                        |
      | component | <            | 16    |                        |
      | component | >            | 15    | MUG-2, MUG-3 and MUG-4 |
      | component | <            | 17    | MUG-2, MUG-3 and MUG-4 |
      | component | >=           | 16    | MUG-2, MUG-3 and MUG-4 |
      | component | <=           | 16    | MUG-2, MUG-3 and MUG-4 |
      | component | =            | 16    | MUG-2, MUG-3 and MUG-4 |
      | component | =            | 0     |                        |
      | component | >            | 0     | MUG-2, MUG-3 and MUG-4 |
    And I hide the filter "supplier"
    And I hide the filter "component"

  Scenario: Successfully filter product without commons attributes
    Given I filter by "component" with operator "=" and value "16"
    And I should be able to use the following filters:
      | filter   | operator     | value | result                        |
      | supplier | is empty     |       | MUG-5                         |
      | supplier | is not empty |       | MUG-1, MUG-2, MUG-3 and MUG-4 |
      | supplier | >            | 12    |                               |
      | supplier | <            | 12    | MUG-2, MUG-3 and MUG-4        |
      | supplier | >            | 11    | MUG-1                         |
      | supplier | <            | 13    | MUG-1, MUG-2, MUG-3 and MUG-4 |
      | supplier | >=           | 12    | MUG-1                         |
      | supplier | <=           | 12    | MUG-1, MUG-2, MUG-3 and MUG-4 |
      | supplier | =            | 12    | MUG-1                         |
      | supplier | =            | 0     |                               |
      | supplier | >            | 0     | MUG-1, MUG-2, MUG-3 and MUG-4 |
    And I hide the filter "supplier"
    And I hide the filter "component"
