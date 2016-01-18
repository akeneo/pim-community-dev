@javascript
Feature: Filter products with multiples metrics filters
  In order to filter products in the catalog
  As a regular user
  I need to be able to filter products with multiples metrics filters

  Background:
    Given the "default" catalog configuration
    And the following family:
      | code      |
      | furniture |
      | library   |
    And the following attributes:
      | code      | label     | type   | useable_as_grid_filter | metric_family | default_metric_unit | decimals_allowed |
      | weight    | Weight    | metric | yes                    | Weight        | GRAM                | yes              |
      | packaging | Packaging | metric | yes                    | Weight        | GRAM                | yes              |
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
    And I am on the products page
    And I show the filter "Packaging"
    And I show the filter "Weight"

  Scenario: Successfully filter products with the sames attributes
    And I filter by "Packaging" with value "> 30 Gram"
    And I should be able to use the following filters:
      | filter | value        | result                 |
      | Weight | = 200 Gram   | MUG-2, MUG-3 and MUG-4 |
      | Weight | >= 200 Gram  | MUG-2, MUG-3 and MUG-4 |
      | Weight | > 199 Gram   | MUG-2, MUG-3 and MUG-4 |
      | Weight | < 200 Gram   |                        |
      | Weight | <= 200 Gram  | MUG-2, MUG-3 and MUG-4 |
      | Weight | < 201 Gram   | MUG-2, MUG-3 and MUG-4 |
      | Weight | empty        | POST-1 and POST-2      |
    And I hide the filter "Packaging"
    And I hide the filter "Weight"

  Scenario: Successfully filter product without commons attributes
    Given I filter by "Weight" with value "> 100 Gram"
    And I should be able to use the following filters:
      | filter    | value      | result                        |
      | Packaging | = 10 Gram  | MUG-1                         |
      | Packaging | >= 10 Gram | MUG-1, MUG-2, MUG-3 and MUG-4 |
      | Packaging | > 9 Gram   | MUG-1, MUG-2, MUG-3 and MUG-4 |
      | Packaging | < 10 Gram  |                               |
      | Packaging | <= 10 Gram | MUG-1                         |
      | Packaging | < 11 Gram  | MUG-1                         |
      | Packaging | empty      | MUG-5                         |
    And I hide the filter "Packaging"
    And I hide the filter "Weight"
    And I hide the filter "Packaging"
    And I hide the filter "Weight"

  @unstable
  Scenario: Successfully filter only one product
    Given I filter by "Packaging" with value "= 10 Gram"
    And I filter by "Weight" with value "= 200 Gram"
    Then the grid should contain 1 elements
    And I should see entities "MUG-1"
    And I hide the filter "Packaging"
    And I hide the filter "Weight"
