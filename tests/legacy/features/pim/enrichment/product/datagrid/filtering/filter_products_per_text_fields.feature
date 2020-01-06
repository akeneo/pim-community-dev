@javascript
Feature: Filter products by text field
  In order to filter products by text attributes in the catalog
  As a regular user
  I need to be able to filter products in the catalog

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Mary"

  @critical
  Scenario: Successfully filter products with special characters value for text attribute
    Given the following attribute:
      | code                  | type             | useable_as_grid_filter | localizable | scopable | group |
      | text_contains         | pim_catalog_text | 1                      | 1           | 1        | other |
      | text_starts_with      | pim_catalog_text | 1                      | 0           | 0        | other |
      | text_does_not_contain | pim_catalog_text | 1                      | 0           | 0        | other |
      | text_equals           | pim_catalog_text | 1                      | 0           | 0        | other |
      | text_empty            | pim_catalog_text | 1                      | 0           | 0        | other |
      | text_is_not_empty     | pim_catalog_text | 1                      | 0           | 0        | other |
    And the following family:
      | code       | attributes |
      | family_foo | text_contains,text_starts_with,text_does_not_contain,text_equals,text_empty,text_is_not_empty |
    And the following products:
      | sku                            | family     | text_contains-en_US-ecommerce |   text_starts_with   | text_does_not_contain | text_equals | text_empty | text_is_not_empty |
      | ok_with_all_filters            | family_foo | HP LA2206xc + WF722A          | HP LA2206xc + WF722A |    foo                |  bar        |            |   not_empty       |
      | not_ok_with_contains           | family_foo | HP LA2206xc + WF666A          | HP LA2206xc + WF722A |    foo                |  bar        |            |   not_empty       |
      | not_ok_with_starts_with        | family_foo | HP LA2206xc + WF722A          | YY LA2206xc + HP     |    foo                |  bar        |            |   not_empty       |
      | not_ok_with_does_not_contain   | family_foo | HP LA2206xc + WF722A          | HP LA2206xc + WF722A |    baz                |  bar        |            |   not_empty       |
      | not_ok_with_equals             | family_foo | HP LA2206xc + WF722A          | HP LA2206xc + WF722A |    foo                |  zoo        |            |   not_empty       |
      | not_ok_with_empty              | family_foo | HP LA2206xc + WF722A          | HP LA2206xc + WF722A |    foo                |  bar        |    foo     |   not_empty       |
      | not_ok_with_not_empty          | family_foo | HP LA2206xc + WF722A          | HP LA2206xc + WF722A |    foo                |  bar        |            |                   |
      | not_ok_with_empty_as_no_family |            | HP LA2206xc + WF722A          | HP LA2206xc + WF722A |    foo                |  bar        |            |   not_empty       |
    And I am on the products grid
    Then the grid should contain 8 elements
    When I filter with the following filters:
      | filter                | operator         | value             |
      | text_empty            | is empty         |                   |
      | text_is_not_empty     | is not empty     |                   |
      | text_contains         | contains         | LA2206xc + WF722A |
      | text_does_not_contain | does not contain | ba                |
      | text_equals           | is equal to      | bar               |
      | text_starts_with      | starts with      | HP                |
    Then the grid should contain 1 elements
    And I should see products ok_with_all_filters
