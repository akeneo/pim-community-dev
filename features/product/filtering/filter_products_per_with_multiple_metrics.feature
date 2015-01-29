@javascript
Feature: Filter products
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
      | code      | label        | type   | useable as grid filter | metric family | default metric unit | decimals allowed |
      | weight    | Weight       | metric | yes                    | Weight        | GRAM                | yes              |
      | packaging | Packaging    | metric | yes                    | Weight        | GRAM                | yes              |
    And the following "weight" attribute options: Black and Green
    And the following "packaging" attribute options: Black and White and Red
    And the following products:
      | sku    | family    | packaging    | weight   |
      | BOOK   | library   |              |          |
      | MUG-1  | furniture | 10 GRAM      | 200 GRAM |
      | MUG-2  | furniture | 50 GRAM      | 200 GRAM |
      | MUG-3  | furniture | 50 GRAM      | 200 GRAM |
      | MUG-4  | furniture | 50 GRAM      | 200 GRAM |
      | MUG-5  | furniture |              | 200 GRAM |
      | POST-1 | furniture | 50 GRAM      |          |
      | POST-2 | furniture | 50 GRAM      |          |
      | POST-3 | furniture | 20 GRAM      |          |
    And the following product groups:
      | code   | label  | axis              | type    | products                           |
      | MUG    | Mug    | weight, packaging | VARIANT | MUG-1, MUG-2, MUG-3, MUG-4, MUG-5  |
      | POSTIT | Postit | packaging         | X_SELL  | POST-1, POST-2, POST-3             |
      | EMPTY  | Empty  |                   | X_SELL  |                                    |
    And I am logged in as "Mary"

  Scenario: Successfully filter products with the sames attributes
    Given I am on the products page
    And I show the filter "Packaging"
    And I filter by "Packaging" with value "> 30 Gram"
    And I show the filter "Weight"
    And I filter by "Weight" with value "= 200 Gram"
    Then the grid should contain 3 elements
    And I should see entities "MUG-2" and "MUG-3" and "MUG-4"
    And I hide the filter "Packaging"
    And I hide the filter "Weight"

  Scenario: Successfully filter product without commons attributes
    Given I am on the products page
    And I show the filter "Packaging"
    And I filter by "Packaging" with value "= 20 Gram"
    And I show the filter "Weight"
    And I filter by "Weight" with value "> 100 Gram"
    Then the grid should contain 0 elements
    And I hide the filter "Packaging"
    And I hide the filter "Weight"

  Scenario: Successfully filter only one product
    Given I am on the products page
    And I show the filter "Packaging"
    And I filter by "Packaging" with value "= 10 Gram"
    And I show the filter "Weight"
    And I filter by "Weight" with value "= 200 Gram"
    Then the grid should contain 1 elements
    And I should see entities "MUG-1"
    And I hide the filter "Packaging"
    And I hide the filter "Weight"
