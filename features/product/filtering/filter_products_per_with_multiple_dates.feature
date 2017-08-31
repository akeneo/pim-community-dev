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
      | code     | label-en_US | type             | useable_as_grid_filter | group |
      | delivery | Delivery    | pim_catalog_date | 1                      | other |
      | supply   | Supply      | pim_catalog_date | 1                      | other |
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
    Given I am on the products grid
    And I show the filter "supply"
    And I filter by "supply" with operator "between" and value "08/01/2014 and 08/01/2014"
    And I show the filter "delivery"
    And I filter by "delivery" with operator "between" and value "05/01/2014 and 05/01/2014"
    Then the grid should contain 3 elements
    And I should see entities "MUG-2" and "MUG-3" and "MUG-4"
    And I hide the filter "supply"
    And I hide the filter "delivery"

  Scenario: Successfully filter product without commons attributes
    Given I am on the products grid
    And I show the filter "supply"
    And I filter by "supply" with operator "between" and value "09/01/2014 and 10/01/2014"
    And I show the filter "delivery"
    And I filter by "delivery" with operator "more than" and value "01/04/2014"
    Then the grid should contain 0 elements
    And I hide the filter "supply"
    And I hide the filter "delivery"

  Scenario: Successfully filter only one product
    Given I am on the products grid
    And I show the filter "supply"
    And I filter by "supply" with operator "less than" and value "02/01/2014"
    And I show the filter "delivery"
    And I filter by "delivery" with operator "between" and value "01/01/2014 and 06/01/2014"
    Then the grid should contain 1 elements
    And I should see entities "MUG-1"
    And I hide the filter "supply"
    And I hide the filter "delivery"
