@javascript
Feature: Filter products per option
  In order to enrich my catalog
  As a regular user
  I need to be able to manually filter products per option

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | label | type         | localizable | scopable | useable_as_grid_filter |
      | color | multiselect  | no          | no       | yes                    |
      | size  | simpleselect | no          | no       | yes                    |
    And the following "color" attribute options: Black, White and Red
    And the following "size" attribute options: S, M and L
    And the following products:
      | sku   | color | size |
      | Shirt |       |      |
      | Sweat |       | M    |
      | Shoes | Black |      |
    And the "Shirt" product has the "color and size" attributes
    And I am logged in as "Mary"

  Scenario: Successfully filter products by a simple option
    Given I am on the products page
    And the grid should contain 3 elements
    Then I should be able to use the following filters:
      | filter | operator | value | result          |
      | size   | in list  | M     | Sweat           |
      | size   | is empty |       | Shirt and Shoes |

  Scenario: Successfully filter products by a multi option
    Given I am on the products page
    And the grid should contain 3 elements
    Then I should be able to use the following filters:
      | filter | operator | value    | result          |
      | color  | in list  | Black    | Shoes           |
      | color  | is empty |          | Shirt and Sweat |

  @jira https://akeneo.atlassian.net/browse/PIM-5802
  Scenario: Successfully keep data previsouly filled on a simple option
    Given I am on the products page
    And the grid should contain 3 elements
    When I show the filter "size"
    And I filter by "size" with value "M"
    And I should see entities Sweat
    And I open the "size" filter
    Then I should see option "[M]" in filter "size"

  @jira https://akeneo.atlassian.net/browse/PIM-5802
  Scenario: Successfully keep data previsouly filled on a multi option
    Given I am on the products page
    And the grid should contain 3 elements
    When I show the filter "color"
    And I filter by "color" with value "Black"
    And I filter by "color" with value "White"
    And I should see entities Shoes
    And I open the "color" filter
    Then I should see option "[Black], [White]" in filter "color"
