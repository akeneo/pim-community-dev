@javascript
Feature: Filter products per price
  In order to filter products in the catalog per price
  As a regular user
  I need to be able to filter products per price

  @critical
  Scenario: Successfully filter products by price
    Given the "default" catalog configuration
    And the following attributes:
      | code                        | scopable | type                         | useable_as_grid_filter | decimals_allowed | group |
      | price_is_empty              | 0        | pim_catalog_price_collection | 1                      | 1                | other |
      | price_is_not_empty          | 0        | pim_catalog_price_collection | 1                      | 1                | other |
      | price_is_superior           | 0        | pim_catalog_price_collection | 1                      | 1                | other |
      | price_is_inferior           | 0        | pim_catalog_price_collection | 1                      | 1                | other |
      | price_is_superior_or_equals | 0        | pim_catalog_price_collection | 1                      | 1                | other |
      | price_is_inferior_or_equals | 0        | pim_catalog_price_collection | 1                      | 1                | other |

    And the following family:
      | code       | attributes |
      | family_foo | price_is_empty,price_is_not_empty,price_is_superior,price_is_inferior,price_is_superior_or_equals,price_is_inferior_or_equals |
    And the following products:
      | sku                             | family     | price_is_empty | price_is_not_empty | price_is_superior | price_is_inferior | price_is_superior_or_equals | price_is_inferior_or_equals |
      | ok_with_all_filters             | family_foo |                |        23.5 EUR    |    24.5 EUR       |      25.5 EUR     |          25.5 EUR           |              26.5 EUR       |
      | not_ok_with_empty               | family_foo |     10 EUR     |        23.5 EUR    |    24.5 EUR       |      25.5 EUR     |          25.5 EUR           |              26.5 EUR       |
      | not_ok_with_not_empty           | family_foo |                |                    |    24.5 EUR       |      25.5 EUR     |          25.5 EUR           |              26.5 EUR       |
      | not_ok_with_superior            | family_foo |                |        23.5 EUR    |    23.5 EUR       |      25.5 EUR     |          25.5 EUR           |              26.5 EUR       |
      | not_ok_with_inferior            | family_foo |                |        23.5 EUR    |    24.5 EUR       |      26.5 EUR     |          25.5 EUR           |              26.5 EUR       |
      | not_ok_with_superior_or_equals  | family_foo |                |        23.5 EUR    |    24.5 EUR       |      26.5 EUR     |          24.5 EUR           |              26.5 EUR       |
      | not_ok_with_inferior_or_equals  | family_foo |                |        23.5 EUR    |    24.5 EUR       |      25.5 EUR     |          25.5 EUR           |              27.5 EUR       |
      | not_ok_with_empty_as_no_family  |            |                |        23.5 EUR    |    24.5 EUR       |      25.5 EUR     |          25.5 EUR           |              26.5 EUR       |
    And I am logged in as "Mary"
    And I am on the products grid
    Then the grid should contain 8 elements
    When I filter with the following filters:
      | filter                           | operator     | value    |
      | price_is_empty                   | is empty     |          |
      | price_is_not_empty               | is not empty |          |
      | price_is_superior                |   >          | 23.5 EUR |
      | price_is_inferior                |   <          | 26.5 EUR |
      | price_is_superior_or_equals      |   >=         | 25.5 EUR |
      | price_is_inferior_or_equals      |   <=         | 26.5 EUR |
    Then the grid should contain 1 elements
    And I should see products ok_with_all_filters
