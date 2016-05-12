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
    And the following "company" attribute options: RedHat, Canonical and Suze
    And the following products:
      | sku    | family    | company   | color |
      | BOOK   | library   |           |       |
      | MUG-1  | furniture | canonical | green |
      | MUG-2  | furniture | suze      | green |
      | MUG-3  | furniture | suze      | green |
      | MUG-4  | furniture | suze      | green |
      | MUG-5  | furniture |           | green |
      | POST-1 | furniture | suze      |       |
      | POST-2 | furniture | suze      |       |
      | POST-3 | furniture | redhat    |       |
    And I am logged in as "Mary"
    And I am on the products page

  Scenario: Successfully filter products with the sames attributes
    Given I show the filter "company"
    And I filter by "company" with operator "in list" value "Suze"
    Then I should be able to use the following filters:
      | filter | operator | value | result                 |
      | color  | in list  | green | MUG-2, MUG-3 and MUG-4 |
      | color  | is empty |       | POST-1 and POST-2      |

  Scenario: Successfully filter product without commons attributes
    Given I show the filter "color"
    And I filter by "color" with operator "in list" value "Green"
    Then I should be able to use the following filters:
      | filter  | operator | value     | result |
      | company | in list  | Canonical | MUG-1  |
      | company | is empty |           | MUG-5  |

  Scenario: Successfully filter only one product
    Given I am on the products page
    When I show the filter "company"
    And I filter by "company" with value "Canonical"
    And I show the filter "color"
    And I filter by "color" with value "Green"
    Then the grid should contain 1 elements
    And I should see entities "MUG-1"
