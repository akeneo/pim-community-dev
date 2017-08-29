@javascript
Feature: Filter products per option
  In order to enrich my catalog
  As a regular user
  I need to be able to manually filter products per option

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | label-en_US | type                     | localizable | scopable | useable_as_grid_filter | group | code  |
      | color       | pim_catalog_multiselect  | 0           | 0        | 1                      | other | color |
      | size        | pim_catalog_simpleselect | 0           | 0        | 1                      | other | size  |
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
      | filter | operator     | value | result |
      | size   | in list      | M     | Sweat  |
      | size   | is empty     |       | Shirt  |
      | size   | is not empty |       | Sweat  |

  Scenario: Successfully filter products by a multi option
    Given I am on the products page
    And the grid should contain 3 elements
    Then I should be able to use the following filters:
      | filter | operator     | value | result |
      | color  | in list      | Black | Shoes  |
      | color  | is empty     |       | Shirt  |
      | color  | is not empty |       | Shoes  |

  @jira https://akeneo.atlassian.net/browse/PIM-5802
  Scenario: Successfully keep data previously filled on a simple option
    Given I am on the products page
    And the grid should contain 3 elements
    When I show the filter "size"
    And I filter by "size" with operator "in list" and value "M"
    And I should see entities Sweat
    Then I should see option "[M]" in filter "size"

  @jira https://akeneo.atlassian.net/browse/PIM-5802
  Scenario: Successfully keep data previously filled on a multi option
    Given I am on the products page
    And the grid should contain 3 elements
    When I show the filter "color"
    And I filter by "color" with operator "in list" and value "Black, White"
    And I should see entities Shoes
    Then I should see options "[Black], [White]" in filter "color"

  @jira https://akeneo.atlassian.net/browse/PIM-6150
  Scenario: Successfully keep the option filter on page reload
    Given I am on the products page
    And the grid should contain 3 elements
    When I show the filter "color"
    And I filter by "color" with operator "in list" and value "Black, White"
    And I reload the page
    Then the criteria of "color" filter should be ""[Black], [White]""
