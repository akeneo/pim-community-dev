@javascript
Feature: Filter products with multiples metrics filters
  In order to filter products in the catalog
  As a regular user
  I need to be able to filter products with multiples metrics filters

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | code      | label-en_US | type               | useable_as_grid_filter | metric_family | default_metric_unit | decimals_allowed | negative_allowed | group |
      | weight    | Weight      | pim_catalog_metric | 1                      | Weight        | GRAM                | 1                | 0                | other |
      | packaging | Packaging   | pim_catalog_metric | 1                      | Weight        | GRAM                | 1                | 0                | other |
    And the following family:
      | code      | attributes       |
      | furniture | weight,packaging |
      | library   | weight,packaging |
    And the following products:
      | sku    | family    | packaging | weight   |
      | BOOK   | library   |           |          |
      | MUG-1  | furniture | 10 GRAM   | 200 GRAM |
      | MUG-2  | furniture | 50 GRAM   | 200 GRAM |
      | MUG-3  | furniture | 50 GRAM   | 200 GRAM |
      | MUG-4  | furniture | 50 GRAM   | 200 GRAM |
      | MUG-5  | furniture |           | 200 GRAM |
      | POST-1 | furniture | 50 GRAM   |          |
      | POST-2 | furniture | 50 GRAM   |          |
      | POST-3 | furniture | 20 GRAM   |          |
    And I am logged in as "Mary"
    And I am on the products grid

  Scenario: Successfully filter products with the sames attributes
    Given I show the filter "packaging"
    And I filter by "packaging" with operator ">" and value "30 Gram"
    Then I should be able to use the following filters:
      | filter | operator     | value    | result                 |
      | weight | =            | 200 Gram | MUG-2, MUG-3 and MUG-4 |
      | weight | >=           | 200 Gram | MUG-2, MUG-3 and MUG-4 |
      | weight | >            | 199 Gram | MUG-2, MUG-3 and MUG-4 |
      | weight | <            | 200 Gram |                        |
      | weight | <=           | 200 Gram | MUG-2, MUG-3 and MUG-4 |
      | weight | <            | 201 Gram | MUG-2, MUG-3 and MUG-4 |
      | weight | is empty     |          | POST-1 and POST-2      |
      | weight | is not empty |          | MUG-2, MUG-3 and MUG-4 |

  Scenario: Successfully filter product without commons attributes
    Given I show the filter "weight"
    And I filter by "weight" with operator ">" and value "100 Gram"
    Then I should be able to use the following filters:
      | filter    | operator     | value   | result                        |
      | packaging | =            | 10 Gram | MUG-1                         |
      | packaging | >=           | 10 Gram | MUG-1, MUG-2, MUG-3 and MUG-4 |
      | packaging | >            | 9 Gram  | MUG-1, MUG-2, MUG-3 and MUG-4 |
      | packaging | <            | 10 Gram |                               |
      | packaging | <=           | 10 Gram | MUG-1                         |
      | packaging | <            | 11 Gram | MUG-1                         |
      | packaging | is empty     |         | MUG-5                         |
      | packaging | is not empty |         | MUG-1, MUG-2, MUG-3 and MUG-4 |

  Scenario: Successfully filter only one product
    Given I show the filter "packaging"
    And I show the filter "weight"
    And I filter by "packaging" with operator "=" and value "10 Gram"
    And I filter by "weight" with operator "=" and value "200 Gram"
    Then the grid should contain 1 elements
    And I should see entities "MUG-1"
    And I hide the filter "packaging"
    And I hide the filter "weight"
