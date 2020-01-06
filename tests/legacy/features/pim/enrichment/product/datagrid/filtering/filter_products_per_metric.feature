@javascript
Feature: Filter products per metric
  In order to filter products in the catalog per metric
  As a regular user
  I need to be able to filter products per metric

  @critical
  Scenario: Successfully filter products by metric
    Given the "default" catalog configuration
    And the following attributes:
      | code                          | scopable | type               | useable_as_grid_filter | metric_family | default_metric_unit | decimals_allowed | negative_allowed | group |
      | metric_is_empty               | 0        | pim_catalog_metric | 1                      | Weight        | GRAM                | 1                | 0                | other |
      | metric_is_not_empty           | 0        | pim_catalog_metric | 1                      | Weight        | GRAM                | 1                | 0                | other |
      | metric_is_superior            | 0        | pim_catalog_metric | 1                      | Weight        | GRAM                | 1                | 0                | other |
      | metric_is_inferior            | 0        | pim_catalog_metric | 1                      | Weight        | GRAM                | 1                | 0                | other |
      | metric_is_superior_or_equals  | 0        | pim_catalog_metric | 1                      | Weight        | GRAM                | 1                | 0                | other |
      | metric_is_inferior_or_equals  | 0        | pim_catalog_metric | 1                      | Weight        | GRAM                | 1                | 0                | other |
    And the following family:
      | code       | attributes |
      | family_foo | metric_is_empty,metric_is_not_empty,metric_is_superior,metric_is_inferior,metric_is_superior_or_equals,metric_is_inferior_or_equals |
    And the following products:
      | sku                             | family     | metric_is_empty | metric_is_not_empty | metric_is_superior | metric_is_inferior | metric_is_superior_or_equals | metric_is_inferior_or_equals |
      | ok_with_all_filters             | family_foo |                 |        120 GRAM     |    121 GRAM        |      122 GRAM      |          123 GRAM            |              124 GRAM        |
      | not_ok_with_empty               | family_foo |     119 GRAM    |        120 GRAM     |    121 GRAM        |      122 GRAM      |          123 GRAM            |              124 GRAM        |
      | not_ok_with_not_empty           | family_foo |                 |                     |    121 GRAM        |      122 GRAM      |          123 GRAM            |              124 GRAM        |
      | not_ok_with_superior            | family_foo |                 |        120 GRAM     |    120 GRAM        |      122 GRAM      |          123 GRAM            |              124 GRAM        |
      | not_ok_with_inferior            | family_foo |                 |        120 GRAM     |    121 GRAM        |      123 GRAM      |          123 GRAM            |              124 GRAM        |
      | not_ok_with_superior_or_equals  | family_foo |                 |        120 GRAM     |    121 GRAM        |      122 GRAM      |          122 GRAM            |              124 GRAM        |
      | not_ok_with_inferior_or_equals  | family_foo |                 |        120 GRAM     |    121 GRAM        |      122 GRAM      |          123 GRAM            |              125 GRAM        |
      | not_ok_with_empty_as_no_family  |            |                 |        120 GRAM     |    121 GRAM        |      122 GRAM      |          123 GRAM            |              124 GRAM        |
    And I am logged in as "Mary"
    And I am on the products grid
    Then the grid should contain 8 elements
    When I filter with the following filters:
      | filter                            | operator     | value    |
      | metric_is_empty                   | is empty     |          |
      | metric_is_not_empty               | is not empty |          |
      | metric_is_superior                |   >          | 120 GRAM |
      | metric_is_inferior                |   <          | 123 GRAM |
      | metric_is_superior_or_equals      |   >=         | 123 GRAM |
      | metric_is_inferior_or_equals      |   <=         | 124 GRAM |
    Then the grid should contain 1 elements
    And I should see products ok_with_all_filters
