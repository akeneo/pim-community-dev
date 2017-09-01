@javascript
Feature: Filter products with multiples simpleselect filters
  In order to filter products in the catalog
  As a regular user
  I need to be able to filter products with multiples simpleselect filters

  Background:
    Given the "default" catalog configuration
    And the following family:
      | code      |
      | furniture |
      | library   |
    And the following attributes:
      | code    | label-en_US | type                     | useable_as_grid_filter | group |
      | color   | Color       | pim_catalog_simpleselect | 1                      | other |
      | company | Company     | pim_catalog_simpleselect | 1                      | other |
    And the following "color" attribute options: Black and Green
    And the following "company" attribute options: Debian and Canonical and Suze
    And the following products:
      | sku    | family    | company   | color |
      | BOOK   | library   |           |       |
      | MUG-1  | furniture | canonical | green |
      | MUG-2  | furniture | suze      | green |
      | MUG-3  | furniture | debian    | green |
      | MUG-4  | furniture | canonical | black |
      | MUG-5  | furniture | suze      | black |
      | POST-1 | furniture | suze      |       |
      | POST-2 | furniture | canonical |       |
      | POST-3 | furniture | debian    |       |
    And I am logged in as "Mary"

  Scenario: Successfully filter products with the sames attributes
    Given I am on the products grid
    And I show the filter "company"
    And I filter by "company" with operator "in list" and value "Suze"
    And I show the filter "color"
    And I filter by "color" with operator "in list" and value "Green"
    Then the grid should contain 1 elements
    And I should see entities "MUG-2"
    And I hide the filter "company"
    And I hide the filter "color"

  Scenario: Successfully filter product without commons attributes
    Given I am on the products grid
    And I show the filter "company"
    And I filter by "company" with operator "in list" and value "Debian"
    And I show the filter "color"
    And I filter by "color" with operator "in list" and value "Black"
    Then the grid should contain 0 elements
    And I hide the filter "company"
    And I hide the filter "color"

  Scenario: Successfully filter only one product
    Given I am on the products grid
    And I show the filter "company"
    And I filter by "company" with operator "in list" and value "Canonical"
    And I show the filter "color"
    And I filter by "color" with operator "in list" and value "Green"
    Then the grid should contain 1 elements
    And I should see entities "MUG-1"
    And I hide the filter "company"
    And I hide the filter "color"
