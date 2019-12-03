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
    And I am logged in as "Mary"

  @critical
  Scenario: Successfully filter products by a simple option
    Given I am on the products grid
    And the grid should contain 3 elements
    Then I should be able to use the following filters:
      | filter | operator     | value | result |
      | size   | in list      | M     | Sweat  |
      | size   | is empty     |       |        |
      | size   | is not empty |       | Sweat  |

  @critical
  Scenario: Successfully filter products by a multi option
    Given I am on the products grid
    And the grid should contain 3 elements
    Then I should be able to use the following filters:
      | filter | operator     | value | result |
      | color  | in list      | Black | Shoes  |
      | color  | is empty     |       |        |
      | color  | is not empty |       | Shoes  |
