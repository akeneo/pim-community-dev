@javascript
Feature: Filter products
  In order to filter products in the catalog
  As a regular user
  I need to be able to filter products with multiples dates filters

  Background:
    Given the "default" catalog configuration
    And the following family:
      | code      |
      | furniture |
      | library   |
    And the following attributes:
      | code     | label    | type | useable_as_grid_filter |
      | delivery | Delivery | date | yes                    |
      | supply   | Supply   | date | yes                    |
    And the following products:
      | sku    | family    | supply     | delivery   |
      | BOOK   | library   |            |            |
      | MUG-1  | furniture | 2014-01-01 | 2014-05-01 |
      | MUG-2  | furniture | 2014-08-01 | 2014-05-01 |
      | MUG-3  | furniture | 2014-08-01 | 2014-05-01 |
      | MUG-4  | furniture | 2014-08-01 | 2014-05-01 |
      | MUG-5  | furniture |            | 2014-05-01 |
      | POST-1 | furniture | 2014-08-01 |            |
      | POST-2 | furniture | 2014-08-01 |            |
      | POST-3 | furniture | 2014-09-01 |            |
    And I am logged in as "Mary"

  Scenario: Successfully filter products with the sames attributes
    Given I am on the products page
    And I show the filter "Supply"
    And I filter by "Supply" with value "between 2014-08-01 and 2014-08-01"
    And I show the filter "Delivery"
    And I filter by "Delivery" with value "between 2014-05-01 and 2014-05-01"
    Then the grid should contain 3 elements
    And I should see entities "MUG-2" and "MUG-3" and "MUG-4"
    And I hide the filter "Supply"
    And I hide the filter "Delivery"

  Scenario: Successfully filter product without commons attributes
    Given I am on the products page
    And I show the filter "Supply"
    And I filter by "Supply" with value "between 2014-09-01 and 2014-10-01"
    And I show the filter "Delivery"
    And I filter by "Delivery" with value "more than 2014-04-01"
    Then the grid should contain 0 elements
    And I hide the filter "Supply"
    And I hide the filter "Delivery"

  Scenario: Successfully filter only one product
    Given I am on the products page
    And I show the filter "Supply"
    And I filter by "Supply" with value "less than 2014-02-01"
    And I show the filter "Delivery"
    And I filter by "Delivery" with value "between 2014-01-01 and 2014-06-01"
    Then the grid should contain 1 elements
    And I should see entities "MUG-1"
    And I hide the filter "Supply"
    And I hide the filter "Delivery"
