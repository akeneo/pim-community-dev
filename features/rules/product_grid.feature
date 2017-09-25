@javascript
Feature: Ensure rules results are shown on datagrid
  In order ease the enrichment of the catalog
  As a regular user
  I need to see on the product datagrid the values updated by rule execution

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | code            | label-en_US     | type                     | localizable | scopable | group | useable_as_grid_filter |
      | simple_select_1 | Simple select 1 | pim_catalog_simpleselect | 0           | 0        | other | 1                      |
      | simple_select_2 | Simple select 2 | pim_catalog_simpleselect | 0           | 0        | other | 1                      |
      | multi_select_1  | Multi select 1  | pim_catalog_multiselect  | 0           | 0        | other | 1                      |
      | multi_select_2  | Multi select 2  | pim_catalog_multiselect  | 0           | 0        | other | 1                      |
    And the following "simple_select_1" attribute options: simple_option_1 and simple_option_2
    And the following "simple_select_2" attribute options: simple_option_1 and simple_option_2
    And the following "multi_select_1" attribute options: multi_option_1 and multi_option_2
    And the following "multi_select_2" attribute options: multi_option_1 and multi_option_2
    And the following family:
      | code        | label-en_US | attributes                                                        |
      | test_family | Test family | sku,simple_select_1,simple_select_2,multi_select_1,multi_select_2 |
    And the following product:
      | sku          | family      | categories | simple_select_1 | multi_select_1                |
      | test-product | test_family | default    | simple_option_1 | multi_option_1,multi_option_2 |
    And I am logged in as "Julia"
    And I am on the "simple_select_1" attribute page
    And I visit the "Values" tab
    And I edit the following attribute option:
      | Code            | en_US                    |
      | simple_option_1 | Simple select 1 option 1 |
      | simple_option_2 | Simple select 1 option 2 |
    And I save the attribute
    And I should not see the text "There are unsaved changes."
    And I am on the "simple_select_2" attribute page
    And I visit the "Values" tab
    And I edit the following attribute option:
      | Code            | en_US                    |
      | simple_option_1 | Simple select 2 option 1 |
      | simple_option_2 | Simple select 2 option 2 |
    And I save the attribute
    And I should not see the text "There are unsaved changes."
    And I am on the "multi_select_1" attribute page
    And I visit the "Values" tab
    And I edit the following attribute option:
      | Code           | en_US                   |
      | multi_option_1 | Multi select 1 option 1 |
      | multi_option_2 | Multi select 1 option 2 |
    And I save the attribute
    And I should not see the text "There are unsaved changes."
    And I am on the "multi_select_2" attribute page
    And I visit the "Values" tab
    And I edit the following attribute option:
      | Code           | en_US                   |
      | multi_option_1 | Multi select 2 option 1 |
      | multi_option_2 | Multi select 2 option 2 |
    And I save the attribute
    And I should not see the text "There are unsaved changes."

  @jira https://akeneo.atlassian.net/browse/PIM-6798
  Scenario: Successfully display updated options in the product datagrid
    Given the following product rule definitions:
      """
      copy_simple_select:
        priority: 10
        conditions:
          - field:    family
            operator: IN
            value:
                - test_family
        actions:
          - type:        copy
            from_field:  simple_select_1
            to_field:    simple_select_2
      copy_multi_select:
        priority: 10
        conditions:
          - field:    family
            operator: IN
            value:
                - test_family
        actions:
          - type:        copy
            from_field:  multi_select_1
            to_field:    multi_select_2
      """
    And the product rule "copy_simple_select" is executed
    And the product rule "copy_multi_select" is executed
    When I am on the products page
    And I display the columns SKU, Simple select 1, Simple select 2, Multi select 1 and Multi select 2
    Then the row "test-product" should contain:
      | column          | value                                            |
      | Simple select 1 | Simple select 1 option 1                         |
      | Simple select 2 | Simple select 2 option 1                         |
      | Multi select 1  | Multi select 1 option 1, Multi select 1 option 2 |
      | Multi select 2  | Multi select 2 option 1, Multi select 2 option 2 |
