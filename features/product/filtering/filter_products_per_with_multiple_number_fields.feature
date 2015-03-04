@javascript
Feature: Filter products
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
      | code      | label     | type   | useable as grid filter |
      | component | Component | number | yes                    |
      | supplier  | Supplier  | number | yes                    |
    And the following "component" attribute options: Black and Green
    And the following "supplier" attribute options: Black and White and Red
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
    And the following product groups:
      | code   | label  | axis                | type    | products                          |
      | MUG    | Mug    | component, supplier | VARIANT | MUG-1, MUG-2, MUG-3, MUG-4, MUG-5 |
      | POSTIT | Postit | supplier            | X_SELL  | POST-1, POST-2, POST-3            |
      | EMPTY  | Empty  |                     | X_SELL  |                                   |
    And I am logged in as "Mary"

  Scenario: Successfully filter products with the sames attributes
    Given I am on the products page
    And I show the filter "Supplier"
    And I filter by "Supplier" with value "03"
    And I show the filter "Component"
    And I filter by "Component" with value "16"
    Then the grid should contain 3 elements
    And I should see entities "MUG-2" and "MUG-3" and "MUG-4"
    And I hide the filter "Supplier"
    And I hide the filter "Component"

  Scenario: Successfully filter product without commons attributes
    Given I am on the products page
    And I show the filter "Supplier"
    And I filter by "Supplier" with value "01"
    And I show the filter "Component"
    And I filter by "Component" with value "16"
    Then the grid should contain 0 elements
    And I hide the filter "Supplier"
    And I hide the filter "Component"

  Scenario: Successfully filter only one product
    Given I am on the products page
    And I show the filter "Supplier"
    And I filter by "Supplier" with value "12"
    And I show the filter "Component"
    And I filter by "Component" with value "16"
    Then the grid should contain 1 elements
    And I should see entities "MUG-1"
    And I hide the filter "Supplier"
    And I hide the filter "Component"
