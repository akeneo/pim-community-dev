@javascript
Feature: Filter products by number field
  In order to filter products by number attributes in the catalog
  As a regular user
  I need to be able to filter products in the catalog

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Mary"

  Scenario: Successfully filter products by empty value for number attributes
    Given the following attributes:
      | label-en_US                     | type               | localizable | scopable | useable_as_grid_filter | decimals_allowed | negative_allowed | group | code                        |
      | count_is_empty                  | pim_catalog_number | 0           | 0        | 1                      | 0                | 0                | other | count_is_empty              |
      | count_is_not_empty              | pim_catalog_number | 0           | 0        | 1                      | 0                | 0                | other | count_is_not_empty          |
      | count_is_superior               | pim_catalog_number | 0           | 0        | 1                      | 0                | 0                | other | count_is_superior           |
      | count_is_inferior               | pim_catalog_number | 0           | 0        | 1                      | 0                | 0                | other | count_is_inferior           |
      | count_is_superior_or_equals     | pim_catalog_number | 0           | 0        | 1                      | 0                | 0                | other | count_is_superior_or_equals |
      | count_is_inferior_or_equals     | pim_catalog_number | 0           | 0        | 1                      | 0                | 0                | other | count_is_inferior_or_equals |
    And the following family:
      | code        | attributes      |
      | family_foo  | count_is_empty,count_is_not_empty,count_is_superior,count_is_inferior,count_is_superior_or_equals,count_is_inferior_or_equals |
    And the following products:
      | sku                             | family     | count_is_empty | count_is_not_empty | count_is_superior | count_is_inferior | count_is_inferior_or_equals | count_is_superior_or_equals |
      | ok_with_all_filters             | family_foo |                |        2           |       3           |         4         |             5               |                 6           |
      | not_ok_with_empty               | family_foo |      100       |        2           |       3           |         4         |             5               |                 6           |
      | not_ok_with_not_empty           | family_foo |                |                    |       3           |         4         |             5               |                 6           |
      | not_ok_with_superior            | family_foo |                |        2           |       2           |         4         |             5               |                 6           |
      | not_ok_with_inferior            | family_foo |                |        2           |       3           |         5         |             5               |                 6           |
      | not_ok_with_superior_or_equals  | family_foo |                |        2           |       3           |         4         |             6               |                 6           |
      | not_ok_with_inferior_or_equals  | family_foo |                |        2           |       3           |         4         |             5               |                 5           |
      | not_ok_with_empty_as_no_family  |            |                |        2           |       3           |         4         |             5               |                 6           |
    And I am on the products grid
    Then the grid should contain 8 elements
    When I filter with the following filters:
      | filter                           | operator     | value |
      | count_is_empty                   | is empty     |       |
      | count_is_not_empty               | is not empty |       |
      | count_is_superior                |   >          |  2    |
      | count_is_inferior                |   <          |  5    |
      | count_is_superior_or_equals      |   >=         |  6    |
      | count_is_inferior_or_equals      |   <=         |  5    |
    Then the grid should contain 1 elements
    And I should see products ok_with_all_filters
