@javascript
Feature: Filter products with multiples multiselect filters
  In order to filter products in the catalog
  As a regular user
  I need to be able to filter products with multiples multiselect filters

  Background:
    Given the "default" catalog configuration
    And the following family:
      | code      |
      | furniture |
      | library   |
    And the following attributes:
      | code    | label   | type        | useable_as_grid_filter |
      | color   | Color   | multiselect | yes                    |
      | company | Company | multiselect | yes                    |
    And the following "color" attribute options: Black and Green
    And the following "company" attribute options: Black and White and Red
    And the following products:
      | sku    | family    | company | color |
      | BOOK   | library   |         |       |
      | MUG-1  | furniture | white   | green |
      | MUG-2  | furniture | red     | green |
      | MUG-3  | furniture | red     | green |
      | MUG-4  | furniture | red     | green |
      | MUG-5  | furniture |         | green |
      | POST-1 | furniture | red     |       |
      | POST-2 | furniture | red     |       |
      | POST-3 | furniture | black   |       |
    And I am logged in as "Mary"
    And I am on the products page
    And I show the filter "Company"
    And I show the filter "Color"

  Scenario: Successfully filter products with the sames attributes
    Given I filter by "Company" with value "Red"
    And I should be able to use the following filters:
      | filter | value    | result                 |
      | Color  | green    | MUG-2, MUG-3 and MUG-4 |
      | Color  | is empty | POST-1 and POST-2      |
    And I hide the filter "Company"
    And I hide the filter "Color"

  Scenario: Successfully filter product without commons attributes
    Given I filter by "Color" with value "Green"
    And I should be able to use the following filters:
      | filter  | value    | result |
      | Company | White    | MUG-1  |
      | Company | is empty | MUG-5  |
    And I hide the filter "Company"
    And I hide the filter "Color"

  Scenario: Successfully filter only one product
    Given I am on the products page
    And I show the filter "Company"
    And I filter by "Company" with value "White"
    And I show the filter "Color"
    And I filter by "Color" with value "Green"
    Then the grid should contain 1 elements
    And I should see entities "MUG-1"
    And I hide the filter "Company"
    And I hide the filter "Color"
